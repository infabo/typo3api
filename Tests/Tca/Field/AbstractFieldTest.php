<?php

namespace Typo3Api\Tca\Field;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Typo3Api\Builder\Context\TableBuilderContext;

class AbstractFieldTest extends UnitTestCase
{
    const STUB_DB_TYPE = "VARCHAR(32) DEFAULT '' NOT NULL";

    protected function setUp(): void
    {
        if (isset($GLOBALS['TCA'])) {
            unset($GLOBALS['TCA']);
        }
    }

    protected function tearDown(): void
    {
        if (isset($GLOBALS['TCA'])) {
            unset($GLOBALS['TCA']);
        }
    }

    public static function validNameProvider(): array
    {
        return [
            ['field_1', 'field_2'],
            ['first_name', 'last_name']
        ];
    }

    /**
     * @param string $name
     * @param array $options
     * @return AbstractField
     */
    protected function createFieldInstance(string $name, array $options = []): AbstractField
    {
        return new AbstractFieldImplementation($name, $options);
    }

    /**
     * @param AbstractField $field
     */
    protected function assertBasicCtrlChange(AbstractField $field)
    {
        $testTable = new TableBuilderContext('stub_table', '1');

        $ctrl = [];
        $field->modifyCtrl($ctrl, $testTable);
        unset($ctrl['EXT']); // remove extension
        $this->assertEmpty($ctrl, "No modification to ctrl is done");
    }

    /**
     * @param AbstractField $field
     */
    protected function assertBasicColumns(AbstractField $field)
    {
        $testTable = new TableBuilderContext('stub_table', '1');

        $this->assertEquals([
            $field->getName() => [
                'label' => $field->getOption('label'),
                'config' => [],
            ]
        ], $field->getColumns($testTable));
    }

    /**
     * @param AbstractField $field
     */
    protected function assertBasicPalette(AbstractField $field): void
    {
        $testTable = new TableBuilderContext('stub_table', '1');
        $this->assertEmpty($field->getPalettes($testTable));
    }

    /**
     * @param AbstractField $field
     */
    protected function assertBasicShowItem(AbstractField $field): void
    {
        $testTable = new TableBuilderContext('stub_table', '1');
        $this->assertEquals($field->getName(), $field->getShowItemString($testTable));
    }

    /**
     * @param AbstractField $field
     */
    protected function assertBasicDatabase(AbstractField $field)
    {
        $testTable = new TableBuilderContext('stub_table', '1');
        $fieldName = $field->getName();
        $this->assertEquals(
            ['stub_table' => ["`$fieldName` " . static::STUB_DB_TYPE]],
            $field->getDbTableDefinitions($testTable)
        );
    }

    /**
     * @dataProvider validNameProvider
     */
    public function testBasicField($fieldName): void
    {
        $field = $this->createFieldInstance($fieldName);

        $this->assertBasicCtrlChange($field);
        $this->assertBasicColumns($field);
        $this->assertBasicPalette($field);
        $this->assertBasicShowItem($field);
        $this->assertBasicDatabase($field);
    }

    public static function invalidNameProvider(): array
    {
        return [
            [''],
            ['CamelCase'],
            ['this_string_is_longer_than_64_characters_which_is_awfully_long_for_a_column_anyways'],
            ['only$basic$latin'],
        ];
    }

    /**
     * @dataProvider invalidNameProvider
     * @param mixed $name
     */
    public function testInvalidName($name): void
    {
        $this->expectException(\Typo3Api\Exception\TcaFieldException::class);
        $this->createFieldInstance($name);
    }

    public static function nameAndLabelProvider(): array
    {
        return [
            ['name', "Name"],
            ['longer_name', "Longer name"],
            ['cat5', "Cat5"],
            ['cat_5', "Cat 5"],
        ];
    }

    /**
     * @dataProvider nameAndLabelProvider
     * @param string $name
     * @param string $expectedLabel
     */
    public function testLabelGeneration(string $name, string $expectedLabel): void
    {
        $field = $this->createFieldInstance($name);
        $this->assertEquals($expectedLabel, $field->getOption('label'));

        $this->assertBasicCtrlChange($field);
        $this->assertBasicColumns($field);
        $this->assertBasicPalette($field);
        $this->assertBasicShowItem($field);
        $this->assertBasicDatabase($field);
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
    public function testExclude(string $fieldName): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $field = $this->createFieldInstance($fieldName, ['exclude' => false]);
        $this->assertArrayNotHasKey('exclude', $field->getColumns($stubTable)[$fieldName]);
        $this->assertBasicCtrlChange($field);
        $this->assertBasicPalette($field);
        $this->assertBasicShowItem($field);
        $this->assertBasicDatabase($field);

        $field = $this->createFieldInstance($fieldName, ['exclude' => true]);
        $this->assertEquals(1, $field->getColumns($stubTable)[$fieldName]['exclude']);
        $this->assertBasicCtrlChange($field);
        $this->assertBasicPalette($field);
        $this->assertBasicShowItem($field);
        $this->assertBasicDatabase($field);
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
        $this->assertEquals('defaultAsReadonly', $field->getColumns($stubTable)[$fieldName]['l10n_display']);
        $this->assertBasicPalette($field);
        $this->assertBasicShowItem($field);
        $this->assertBasicDatabase($field);
    }

    /**
     * @dataProvider validNameProvider
     * @param string $fieldName
     */
    public function testDisplayCondition(string $fieldName): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $field = $this->createFieldInstance($fieldName, ['displayCond' => 'some condition']);
        $this->assertBasicCtrlChange($field);
        $this->assertEquals('some condition', $field->getColumns($stubTable)[$fieldName]['displayCond']);
        $this->assertBasicPalette($field);
        $this->assertBasicShowItem($field);
        $this->assertBasicDatabase($field);
    }

    /**
     * @dataProvider validNameProvider
     * @param string $fieldName
     */
    public function testUseAsLabel(string $fieldName): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $field = $this->createFieldInstance($fieldName, ['useAsLabel' => true]);

        $ctrl = [];
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals($fieldName, $ctrl['label']);
        $this->assertBasicColumns($field);
        $this->assertBasicPalette($field);
        $this->assertBasicShowItem($field);
        $this->assertBasicDatabase($field);

        $ctrl = ['label' => 'other_field'];
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals('other_field', $ctrl['label']);
        $this->assertEquals($fieldName, $ctrl['label_alt']);

        $ctrl = ['label' => 'other_field_1', 'label_alt' => 'other_field_2'];
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals('other_field_1', $ctrl['label']);
        $this->assertEquals('other_field_2, ' . $fieldName, $ctrl['label_alt']);
    }

    /**
     * @dataProvider validNameProvider
     * @param string $fieldName
     */
    public function testSearchField(string $fieldName): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $field = $this->createFieldInstance($fieldName, ['searchField' => false]);
        $ctrl = [];
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertArrayNotHasKey('search_field', $ctrl);

        $field = $this->createFieldInstance($fieldName, ['searchField' => true]);
        $ctrl = [];
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals($fieldName, $ctrl['searchFields']);

        $field = $this->createFieldInstance($fieldName, ['searchField' => true]);
        $ctrl = ['searchFields' => 'other_field'];
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals('other_field, ' . $fieldName, $ctrl['searchFields']);
    }

    /**
     * @dataProvider validNameProvider
     * @param string $fieldName
     */
    public function testLabel(string $fieldName): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $field = $this->createFieldInstance($fieldName, ['useAsLabel' => false]);
        $ctrl = ['label' => 'uid'];
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals('uid', $ctrl['label']);

        $field = $this->createFieldInstance($fieldName, ['useAsLabel' => true]);
        $ctrl = ['label' => 'uid'];
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals($fieldName, $ctrl['label']);
    }
}
