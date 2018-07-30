<?php

namespace Typo3Api\Tca\Field;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use Typo3Api\Builder\Context\TableBuilderContext;

class InlineRelationFieldTest extends AbstractFieldTest
{
    const STUB_DB_TYPE = "TINYINT(3) UNSIGNED DEFAULT '0' NOT NULL";

    public function setUp()
    {
        parent::setUp();
        $GLOBALS['TCA']['tx_typo3api_foreign_table'] = [
            'ctrl' => [],
        ];
    }

    protected function createFieldInstance(string $name, array $options = []): AbstractField
    {
        return new InlineRelationField($name, $options + ['foreign_table' => 'tx_typo3api_foreign_table']);
    }

    protected function assertBasicCtrlChange(AbstractField $field)
    {
        $GLOBALS['TCA'][$field->getOption('foreign_table')]['ctrl'] = [];
        $testTable = new TableBuilderContext('stub_table', '1');

        $ctrl = [];
        $field->modifyCtrl($ctrl, $testTable);

        list($tca) = GeneralUtility::makeInstance(Dispatcher::class)->dispatch(ExtensionManagementUtility::class, 'tcaIsBeingBuilt', [$GLOBALS['TCA']]);
        $this->assertNotEquals($GLOBALS['TCA'], $tca);
        $GLOBALS['TCA'] = $tca;

        unset($ctrl['EXT']); // remove extension
        $this->assertEmpty($ctrl, "No modification to ctrl is done");
        $this->assertEquals(
            ['hideTable' => true],
            $GLOBALS['TCA'][$field->getOption('foreign_table')]['ctrl'],
            "Hide table"
        );
    }

    protected function assertBasicDatabase(AbstractField $field)
    {
        $testTable = new TableBuilderContext('stub_table', '1');
        $fieldName = $field->getName();
        $this->assertEquals(
            [
                'tx_typo3api_foreign_table' => [
                    '`parent_uid` INT(11) DEFAULT \'0\' NOT NULL',
                    'KEY `parent_uid`(`parent_uid`)',
                ],
                'stub_table' => ["`$fieldName` " . static::STUB_DB_TYPE]
            ],
            $field->getDbTableDefinitions($testTable)
        );
    }

    protected function assertBasicColumns(AbstractField $field)
    {
        $testTable = new TableBuilderContext('stub_table', '1');

        $GLOBALS['TCA'][$testTable->getTableName()]['columns'] = $field->getColumns($testTable);
        list($tca) = GeneralUtility::makeInstance(Dispatcher::class)->dispatch(ExtensionManagementUtility::class, 'tcaIsBeingBuilt', [$GLOBALS['TCA']]);
        $this->assertNotEquals($GLOBALS['TCA'], $tca);
        $GLOBALS['TCA'] = $tca;

        $this->assertEquals([
            'label' => $field->getOption('label'),
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_typo3api_foreign_table',
                'foreign_field' => 'parent_uid',
                'foreign_sortby' => null,
                'minitems' => 0,
                'maxitems' => 100,
                'behaviour' => [
                    'enableCascadingDelete' => true,
                    'localizeChildrenAtParentLocalization' => false,
                ],
                'appearance' => [
                    'collapseAll' => 1,
                    'useSortable' => false,
                    'showPossibleLocalizationRecords' => false,
                    'showRemovedLocalizationRecords' => false,
                    'showAllLocalizationLink' => false,
                    'showSynchronizationLink' => false,
                    'enabledControls' => [
                        'info' => true,
                        'new' => true,
                        'dragdrop' => false,
                        'sort' => false,
                        'hide' => false,
                        'delete' => true,
                        'localize' => false,
                    ],
                ],
            ],
        ], $tca[$testTable->getTableName()]['columns'][$field->getName()]);
    }

    /**
     * @dataProvider validNameProvider
     * @param string $fieldName
     */
    public function testIndex(string $fieldName)
    {
        $testTable = new TableBuilderContext('stub_table', '1');
        $field = $this->createFieldInstance($fieldName, ['index' => true]);

        $this->assertBasicCtrlChange($field);
        $this->assertBasicColumns($field);
        $this->assertBasicPalette($field);
        $this->assertBasicShowItem($field);
        $this->assertEquals(
            [
                'tx_typo3api_foreign_table' => [
                    '`parent_uid` INT(11) DEFAULT \'0\' NOT NULL',
                    'KEY `parent_uid`(`parent_uid`)',
                ],
                'stub_table' => [
                    "`$fieldName` " . static::STUB_DB_TYPE,
                    "INDEX `$fieldName`(`$fieldName`)"
                ]
            ],
            $field->getDbTableDefinitions($testTable)
        );
    }

    /**
     * @dataProvider validNameProvider
     * @param string $fieldName
     */
    public function testLocalize(string $fieldName)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $field = $this->createFieldInstance($fieldName, ['localize' => true]);
        $this->assertBasicCtrlChange($field);
        $this->assertArrayNotHasKey('l10n_mode', $field->getColumns($stubTable)[$fieldName]);
        $this->assertArrayNotHasKey('l10n_display', $field->getColumns($stubTable)[$fieldName]);
        $this->assertBasicPalette($field);
        $this->assertBasicShowItem($field);
        $this->assertBasicDatabase($field);

        $field = $this->createFieldInstance($fieldName, ['localize' => false]);
        $this->assertBasicCtrlChange($field);
        $this->assertEquals('exclude', $field->getColumns($stubTable)[$fieldName]['l10n_mode']);
        $this->assertFalse(isset($field->getColumns($stubTable)[$fieldName]['l10n_display']));
        $this->assertBasicPalette($field);
        $this->assertBasicShowItem($field);
        $this->assertBasicDatabase($field);
    }

}