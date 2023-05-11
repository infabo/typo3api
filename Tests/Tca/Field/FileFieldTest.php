<?php

namespace Typo3Api\Tca\Field;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Typo3Api\Builder\Context\TableBuilderContext;

class FileFieldTest extends AbstractFieldTest
{
    protected function createFieldInstance(string $name, array $options = []): AbstractField
    {
        return new FileField($name, $options);
    }

    protected function assertBasicColumns(AbstractField $field)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $this->assertEquals([
            $field->getName() => [
                'label' => $field->getOption('label'),
                'config' => ExtensionManagementUtility::getFileFieldTCAConfig($field->getName(), [
                    'minitems' => 0,
                    'maxitems' => 100,
                    'appearance' => [
                        'collapseAll' => true,
                        'showPossibleLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true,
                        'enabledControls' => [
                            'localize' => true,
                        ],
                    ],
                ]),
            ],
        ], $field->getColumns($stubTable));
    }

    /**
     * @param AbstractField $field
     */
    protected function assertBasicDatabase(AbstractField $field)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $fieldName = $field->getName();
        $this->assertEquals(
            ['stub_table' => ["`$fieldName` TINYINT(3) UNSIGNED DEFAULT '0' NOT NULL"]],
            $field->getDbTableDefinitions($stubTable)
        );
    }

    /**
     * @dataProvider validNameProvider
     *
     * @param string $fieldName
     */
    public function testIndex(string $fieldName)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $field = $this->createFieldInstance($fieldName, ['index' => true]);

        $this->assertBasicCtrlChange($field);
        $this->assertBasicColumns($field);
        $this->assertBasicPalette($field);
        $this->assertBasicShowItem($field);
        $this->assertEquals(
            [
                'stub_table' => [
                    "`$fieldName` TINYINT(3) UNSIGNED DEFAULT '0' NOT NULL",
                    "INDEX `$fieldName`(`$fieldName`)",
                ],
            ],
            $field->getDbTableDefinitions($stubTable)
        );
    }

    public function testAllowedFileExt(): void
    {
        $testTable = new TableBuilderContext('stub_table', '1');

        $field = $this->createFieldInstance('field', ['allowedFileExtensions' => 'jpg, png']);
        $this->assertEquals('jpg,png', $field->getColumns($testTable)['field']['config']['filter'][0]['parameters']['allowedFileExtensions']);

        $field = $this->createFieldInstance('field', ['allowedFileExtensions' => ['jpg', 'png']]);
        $this->assertEquals('jpg,png', $field->getColumns($testTable)['field']['config']['filter'][0]['parameters']['allowedFileExtensions']);
    }
}
