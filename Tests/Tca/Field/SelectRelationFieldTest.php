<?php

namespace Typo3Api\Tca\Field;


use Typo3Api\Builder\Context\TableBuilderContext;

class SelectRelationFieldTest extends AbstractFieldTest
{
    const STUB_DB_TYPE = "INT(11) DEFAULT '0' NOT NULL";

    protected function createFieldInstance(string $name, array $options = []): AbstractField
    {
        return new SelectRelationField($name, $options + ['foreign_table' => 'tx_typo3api_foreign_table']);
    }

    protected function assertBasicColumns(AbstractField $field)
    {
        $testTable = new TableBuilderContext('stub_table', '1');

        $this->assertEquals([
            $field->getName() => [
                'label' => $field->getOption('label'),
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'foreign_table' => 'tx_typo3api_foreign_table',
                    'foreign_table_where' => '',
                    'items' => [
                        ['', '0'],
                    ],
                ],
            ],
        ], $field->getColumns($testTable));
    }

    public function testForeignTableSorting(): void
    {
        $GLOBALS['TCA']['tx_typo3api_foreign_table'] = ['ctrl' => ['sortby' => 'sorting']];
        $testTable = new TableBuilderContext('stub_table', '1');
        $field = $this->createFieldInstance('field');
        $this->assertEquals(
            'ORDER BY tx_typo3api_foreign_table.sorting',
            $field->getColumns($testTable)['field']['config']['foreign_table_where']
        );

        $GLOBALS['TCA']['tx_typo3api_foreign_table'] = ['ctrl' => ['sortby' => 'sorting']];
        $field = $this->createFieldInstance('field', ['foreign_table_where' => 'pid = 5']);
        $this->assertEquals(
            'pid = 5 ORDER BY tx_typo3api_foreign_table.sorting',
            $field->getColumns($testTable)['field']['config']['foreign_table_where']
        );

        $GLOBALS['TCA']['tx_typo3api_foreign_table'] = ['ctrl' => ['default_sortby' => 'sorting ASC']];
        $field = $this->createFieldInstance('field', ['foreign_table_where' => 'pid = 5']);
        $this->assertEquals(
            'pid = 5 ORDER BY tx_typo3api_foreign_table.sorting ASC',
            $field->getColumns($testTable)['field']['config']['foreign_table_where']
        );
    }
}
