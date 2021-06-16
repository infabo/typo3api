<?php

namespace Typo3Api\Hook;

use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\EventListener\SqlSchemaListener;
use Typo3Api\PreparationForTypo3;
use Typo3Api\Tca\CustomConfiguration;

class SqlSchemaListenerTest extends UnitTestCase
{
    use PreparationForTypo3;

    public function testEmptyModify(): void
    {
        $schemaListener = new SqlSchemaListener();
        $event = new AlterTableDefinitionStatementsEvent([]);
        $schemaListener->__invoke($event);
        $sql = $event->getSqlData();
        $this->assertEquals([], $sql);
    }

    public function testCreateTable(): void
    {
        $testTable = new TableBuilderContext('test_table', '1');

        $fieldDefinition = '`title` VARCHAR(32) DEFAULT "" NOT NULL';
        $configuration = new CustomConfiguration(['dbTableDefinition' => ['test_table' => [$fieldDefinition]]]);
        $GLOBALS['TCA']['test_table']['ctrl']['EXT']['typo3api']['sql'] = $configuration->getDbTableDefinitions($testTable);

        $schemaListener = new SqlSchemaListener();
        $event = new AlterTableDefinitionStatementsEvent([]);
        $schemaListener->__invoke($event);
        $sql = $event->getSqlData();
        $this->assertEquals(["CREATE TABLE `test_table` (\n$fieldDefinition\n);"], $sql);
    }

    public function testModifyTable(): void
    {
        $testTable = new TableBuilderContext('test_table', '1');

        $previousDefinition = "CREATE TABLE `test_table` (uid int(11) NOT NULL auto_increment, PRIMARY KEY (uid));";
        $fieldDefinition = '`title` VARCHAR(32) DEFAULT "" NOT NULL';
        $configuration = new CustomConfiguration(['dbTableDefinition' => ['test_table' => [$fieldDefinition]]]);
        $GLOBALS['TCA']['test_table']['ctrl']['EXT']['typo3api']['sql'] = $configuration->getDbTableDefinitions($testTable);

        $schemaListener = new SqlSchemaListener();
        $event = new AlterTableDefinitionStatementsEvent([$previousDefinition]);
        $schemaListener->__invoke($event);
        $sql = $event->getSqlData();

        $this->assertEquals(
            [
                $previousDefinition,
                "CREATE TABLE `test_table` (\n$fieldDefinition\n);"
            ]
            , $sql);
    }
}
