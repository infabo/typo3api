<?php

namespace Typo3Api\Builder;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Typo3Api\PreparationForTypo3;

class TableBuilderTest extends UnitTestCase
{
    use PreparationForTypo3;

    public function testCreateTable(): void
    {
        TableBuilder::create('test_table');
        $this->assertArrayHasKey('ctrl', $GLOBALS['TCA']['test_table']);
        $this->assertArrayHasKey('columns', $GLOBALS['TCA']['test_table']);
        $this->assertArrayHasKey('types', $GLOBALS['TCA']['test_table']);
        $this->assertArrayHasKey('palettes', $GLOBALS['TCA']['test_table']);
    }
}
