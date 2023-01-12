<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;

/**
 * This is a configuration containing multiple configurations.
 * There are some optimizations done in the table builder.
 * Some methods are final for that reason.
 */
class CompoundTcaConfiguration implements TcaConfigurationInterface, \IteratorAggregate
{
    /**
     * This field is protected so extending implementations can access it.
     *
     * @var TcaConfigurationInterface[]
     */
    protected array $children;

    public function __construct(array $children = [])
    {
        $this->children = $children;
    }

    /**
     * @return TcaConfigurationInterface[]
     */
    final public function getContainedConfigurations(): array
    {
        return $this->children;
    }

    final public function getIterator(): \Iterator
    {
        yield from $this->children;
    }

    final public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder): void
    {
        foreach ($this->children as $child) {
            $child->modifyCtrl($ctrl, $tcaBuilder);
        }
    }

    final public function getColumns(TcaBuilderContext $tcaBuilder): array
    {
        $columns = [];

        foreach ($this->children as $child) {
            foreach ($child->getColumns($tcaBuilder) as $columnName => $columnDefinition) {
                $columns[$columnName] = $columnDefinition;
            }
        }

        return $columns;
    }

    final public function getDbTableDefinitions(TableBuilderContext $tableBuilder): array
    {
        $tableDefinitions = [];

        foreach ($this->children as $child) {
            foreach ($child->getDbTableDefinitions($tableBuilder) as $table => $columns) {
                if (!isset($tableDefinitions[$table])) {
                    $tableDefinitions[$table] = $columns;
                } else {
                    foreach ($columns as $column) {
                        $tableDefinitions[$table][] = $column;
                    }
                }
            }
        }

        return $tableDefinitions;
    }

    public function getPalettes(TcaBuilderContext $tcaBuilder): array
    {
        return [];
    }

    public function getShowItemString(TcaBuilderContext $tcaBuilder): string
    {
        return implode(',', array_map(static fn(TcaConfigurationInterface $configuration) => $configuration->getShowItemString($tcaBuilder), $this->children));
    }
}
