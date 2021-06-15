<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;

class SortingConfiguration implements TcaConfigurationInterface
{
    public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder)
    {
        $ctrl['sortby'] = 'sorting';
    }

    public function getColumns(TcaBuilderContext $tcaBuilder): array
    {
        return [
            'sorting' => [
                'config' => [
                    'type' => 'passthrough'
                ]
            ]
        ];
    }

    public function getPalettes(TcaBuilderContext $tcaBuilder): array
    {
        return [];
    }

    public function getShowItemString(TcaBuilderContext $tcaBuilder): string
    {
        return '';
    }

    public function getDbTableDefinitions(TableBuilderContext $tableBuilder): array
    {
        return [
            $tableBuilder->getTableName() => [
                "sorting int(11) DEFAULT '0' NOT NULL",

                // sorting is always local to the pid so putting that in the index might help a lot
                "INDEX sorting (pid, sorting ASC)",
            ]
        ];
    }
}
