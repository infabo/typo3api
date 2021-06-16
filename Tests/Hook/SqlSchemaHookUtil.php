<?php

namespace Typo3Api\Hook;

use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;
use Typo3Api\EventListener\SqlSchemaListener;

trait SqlSchemaHookUtil
{
    public function assertSqlInserted(array $expected, $message = ''): void
    {
        $schemaListener = new SqlSchemaListener();
        $event = new AlterTableDefinitionStatementsEvent([]);
        $schemaListener->__invoke($event);
        $sql = $event->getSqlData();

        $definitions = array_map(function ($tableName, $fieldDefinitions) {
            return "CREATE TABLE `$tableName` (\n" . implode(",\n", $fieldDefinitions) . "\n);";
        }, array_keys($expected), array_values($expected));
        $this->assertEquals($definitions, $sql, $message);
    }
}
