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
        return [];
    }
}
