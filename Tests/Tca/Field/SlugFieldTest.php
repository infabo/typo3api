<?php

namespace Typo3Api\Tca\Field;


use Typo3Api\Builder\Context\TableBuilderContext;

class SlugFieldTest extends AbstractFieldTest
{
    const STUB_DB_TYPE = "VARCHAR(2048) DEFAULT '' NOT NULL";

    #[\Override]
    public function createFieldInstance(string $name, array $options = [], $extendDefaults = true): AbstractField
    {
        if ($extendDefaults) {
            $options = ['fields' => ['title']] + $options;
        }

        return new SlugField($name, $options);
    }

    #[\Override]
    protected function assertBasicColumns(AbstractField $field)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $this->assertEquals([
            $field->getName() => [
                'label' => $field->getOption('label'),
                'config' => [
                    'type' => 'slug',
                    'size' => 50,
                    'generatorOptions' => [
                        'fields' => ['title'],
                        'fieldSeparator' => '/',
                    ],
                    'fallbackCharacter' => '-',
                    'prependSlash' => false,
                    'default' => '',
                    'eval' => 'uniqueInSite',
                ]
            ]
        ], $field->getColumns($stubTable));
    }

    public function testFieldsIsRequired(): void
    {
        $this->expectException(\Typo3Api\Exception\TcaFieldException::class);
        $this->createFieldInstance('slug', [], false);
    }

    public function testSingleFieldStringIsNormalizedToArray(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $field = $this->createFieldInstance('slug', ['fields' => 'title'], false);
        $config = $field->getColumns($stubTable)['slug']['config'];
        $this->assertEquals(['title'], $config['generatorOptions']['fields']);
    }

    public function testGeneratorOptions(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $field = $this->createFieldInstance('slug', [
            'fields' => [['nav_title', 'title']],
            'fieldSeparator' => '-',
            'prefixParentPageSlug' => true,
            'replacements' => ['/' => ''],
            'regexReplacements' => ['/foo/' => 'bar'],
            'postModifiers' => ['Some\\Class->method'],
        ], false);
        $config = $field->getColumns($stubTable)['slug']['config'];
        $this->assertEquals([
            'fields' => [['nav_title', 'title']],
            'fieldSeparator' => '-',
            'prefixParentPageSlug' => true,
            'replacements' => ['/' => ''],
            'regexReplacements' => ['/foo/' => 'bar'],
            'postModifiers' => ['Some\\Class->method'],
        ], $config['generatorOptions']);
    }

    public function testEvalCanBeDisabled(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $field = $this->createFieldInstance('slug', ['fields' => ['title'], 'eval' => ''], false);
        $this->assertArrayNotHasKey('eval', $field->getColumns($stubTable)['slug']['config']);
    }

    public function testRequiredReadOnlyAndPrependSlash(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $field = $this->createFieldInstance('slug', [
            'fields' => ['title'],
            'required' => true,
            'readOnly' => true,
            'prependSlash' => true,
        ], false);
        $config = $field->getColumns($stubTable)['slug']['config'];
        $this->assertTrue($config['required']);
        $this->assertTrue($config['readOnly']);
        $this->assertTrue($config['prependSlash']);
    }

    public function testAppearancePrefix(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $field = $this->createFieldInstance('slug', [
            'fields' => ['title'],
            'appearancePrefix' => 'Some\\Class->prefix',
        ], false);
        $config = $field->getColumns($stubTable)['slug']['config'];
        $this->assertEquals(['prefix' => 'Some\\Class->prefix'], $config['appearance']);
    }

    public function testDbColumnSize(): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $field = $this->createFieldInstance('slug', ['fields' => ['title'], 'max' => 512], false);
        $this->assertEquals(
            "`slug` VARCHAR(512) DEFAULT '' NOT NULL",
            $field->getDbTableDefinitions($stubTable)['stub_table'][0]
        );
    }
}
