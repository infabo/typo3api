<?php

declare(strict_types=1);

namespace Typo3Api\EventListener;

use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

class SqlSchemaListener
{
    public function __invoke(AlterTableDefinitionStatementsEvent $event): void
    {
        $map = [];

        foreach ($GLOBALS['TCA'] as $tableDefinition) {
            if (!isset($tableDefinition['ctrl']['EXT']['typo3api']['sql'])) {
                continue;
            }

            foreach ($tableDefinition['ctrl']['EXT']['typo3api']['sql'] as $table => $fieldDefinitions) {
                if (!isset($map[$table])) {
                    $map[$table] = [];
                }

                foreach ($fieldDefinitions as $fieldDefinition) {
                    $map[$table][] = $fieldDefinition;
                }
            }
        }

        foreach ($map as $tableName => $definitions) {
            $event->addSqlData("CREATE TABLE `$tableName` (\n" . implode(",\n", $definitions) . "\n);");
        }
    }
}