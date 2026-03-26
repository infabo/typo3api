<?php

namespace Typo3Api\Tca;


class NamedPaletteTest extends CompoundTcaConfigurationTest
{
    const GENERAL = '--div--; LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, ';

    #[\Override]
    protected function createInstance(TcaConfigurationInterface ...$instances): CompoundTcaConfiguration
    {
        return new NamedPalette('Named palette', $instances);
    }

    #[\Override]
    public function testAddingTwoFields(): void
    {
        parent::testAddingTwoFields();
        $this->assertEquals(
            ['showitem' => 'field_1, field_2'],
            $GLOBALS['TCA']['test_table']['palettes']['field_1_field_2']
        );
        $this->assertEquals(
            ['showitem' => self::GENERAL . '--palette--; Named palette; field_1_field_2'],
            $GLOBALS['TCA']['test_table']['types']['1']
        );
    }

    #[\Override]
    public function testMergeTwoFields(): void
    {
        parent::testMergeTwoFields();
        $this->assertEquals(
            ['showitem' => 'field_1, field_2'],
            $GLOBALS['TCA']['test_table']['palettes']['field_1_field_2']
        );
        $this->assertEquals(
            ['showitem' => self::GENERAL . '--palette--; Named palette; field_1_field_2'],
            $GLOBALS['TCA']['test_table']['types']['1']
        );
    }
}