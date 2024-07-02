<?php

namespace Typo3Api\Tca\Field;


use Typo3Api\Builder\Context\TableBuilderContext;

class SelectFieldTest extends AbstractFieldTest
{
    const STUB_DB_TYPE = "VARCHAR(1) DEFAULT '' NOT NULL";

    protected function createFieldInstance(string $name, array $options = []): AbstractField
    {
        return new SelectField($name, $options);
    }

    /**
     * @param AbstractField $field
     */
    protected function assertBasicColumns(AbstractField $field)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $this->assertEquals([
            $field->getName() => [
                'label' => $field->getOption('label'),
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['label' => '', 'value' => '']
                    ],
                    'default' => '',
                ],
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
            ]
        ], $field->getColumns($stubTable));
    }

    public function testItems(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $items = [
            ['label' => 'label', 'value' =>  'value'],
            ['label' => 'divider', 'value' =>  '--div--'],
            ['label' => 'label2', 'value' =>  'value2'],
        ];
        $field = $this->createFieldInstance('some_field', [
            'items' => $items
        ]);

        $this->assertEquals([
            ['label' => '', 'value' => ''],
            ['label' => 'label', 'value' => 'value'],
            ['label' => 'divider', 'value' => '--div--'],
            ['label' => 'label2', 'value' => 'value2'],
        ], $field->getColumns($stubTable)['some_field']['config']['items']);
    }

    public function testValues(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $field = $this->createFieldInstance('some_field', [
            'values' => ['value', 'value2']
        ]);

        $this->assertEquals([
            ['label' => '', 'value' => ''],
            ['label' => 'Value', 'value' => 'value'],
            ['label' => 'Value2', 'value' => 'value2'],
        ], $field->getColumns($stubTable)['some_field']['config']['items']);
    }

    public function testRequired(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $field = $this->createFieldInstance('some_field', [
            'values' => ['value', 'value2'],
            'required' => true
        ]);

        $this->assertEquals([
            ['label' => 'Value', 'value' => 'value'],
            ['label' => 'Value2', 'value' => 'value2'],
        ], $field->getColumns($stubTable)['some_field']['config']['items']);
    }

    public function testItemProcType(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $field = $this->createFieldInstance('some_field', [
            'itemsProcFunc' => 'some-func'
        ]);
        $this->assertEquals(
            [
                'itemsProcFunc' => 'some-func',
                'items' => [['label' => '', 'value' => '']],
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => $field->getOption('default'),
            ],
            $field->getFieldTcaConfig($stubTable)
        );
        $this->assertEquals(
            [
                'stub_table' => ["`some_field` VARCHAR(30) DEFAULT '' NOT NULL"]
            ],
            $field->getDbTableDefinitions($stubTable)
        );
    }
}
