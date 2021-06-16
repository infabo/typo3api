<?php

namespace Typo3Api\Tca\Field;


use Typo3Api\Builder\Context\TableBuilderContext;

class InputFieldTest extends AbstractFieldTest
{
    public function createFieldInstance(string $name, array $options = [], $extendDefaults = true): AbstractField
    {
        if ($extendDefaults) {
            $options = ['dbType' => self::STUB_DB_TYPE] + $options;
        }

        return new InputField($name, $options);
    }

    public function assertBasicCtrlChange(AbstractField $field)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $ctrl = [];
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals([
            'searchFields' => $field->getName(),
            'label' => $field->getName(),
        ], $ctrl);
    }

    protected function assertBasicColumns(AbstractField $field)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $this->assertEquals([
            $field->getName() => [
                'label' => $field->getOption('label'),
                'config' => [
                    'type' => 'input',
                    'size' => 25,
                    'max' => 50,
                    'eval' => 'trim',
                    'default' => '',
                ]
            ]
        ], $field->getColumns($stubTable));
    }

    public static function differentSizeProvider(): array
    {
        return [
            [10],
            [50],
            [250],
            [500],
        ];
    }

    /**
     * @dataProvider differentSizeProvider
     * @param int $size
     */
    public function testDifferentSizes(int $size): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $fieldName = 'test_field_1';
        $field = $this->createFieldInstance($fieldName, ['max' => $size], false);
        $this->assertEquals($size, $field->getColumns($stubTable)[$fieldName]['config']['max']);
        $this->assertEquals($size / 2, $field->getColumns($stubTable)[$fieldName]['config']['size']);
        $this->assertEquals("`$fieldName` VARCHAR($size) DEFAULT '' NOT NULL", $field->getDbTableDefinitions($stubTable)[$stubTable->getTableName()][0]);
    }

    public function testDefault(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $fieldName = 'some_field';

        $default = 'some default';
        $field = $this->createFieldInstance($fieldName, ['default' => $default], false);
        $this->assertBasicCtrlChange($field);
        $this->assertEquals($default, $field->getColumns($stubTable)[$fieldName]['config']['default']);
        $this->assertEquals("`$fieldName` VARCHAR(50) DEFAULT '$default' NOT NULL", $field->getDbTableDefinitions($stubTable)[$stubTable->getTableName()][0]);

        $field = $this->createFieldInstance($fieldName, ['default' => ''], false);
        $this->assertBasicCtrlChange($field);
        $this->assertEquals('', $field->getColumns($stubTable)[$fieldName]['config']['default']);
        $this->assertEquals("`$fieldName` VARCHAR(50) DEFAULT '' NOT NULL", $field->getDbTableDefinitions($stubTable)[$stubTable->getTableName()][0]);
    }

    public function testPlaceholder(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $fieldName = 'some_field';

        $field = $this->createFieldInstance($fieldName, ['placeholder' => 'eg. Some Street']);
        $this->assertBasicCtrlChange($field);
        $this->assertEquals('eg. Some Street', $field->getColumns($stubTable)[$fieldName]['config']['placeholder']);

        $field = $this->createFieldInstance($fieldName, ['placeholder' => '']);
        $this->assertBasicCtrlChange($field);
        $this->assertEquals('', $field->getColumns($stubTable)[$fieldName]['config']['placeholder']);

        $field = $this->createFieldInstance($fieldName, ['placeholder' => null]);
        $this->assertBasicCtrlChange($field);
        $this->assertArrayNotHasKey('placeholder', $field->getColumns($stubTable)[$fieldName]['config']);

    }
}
