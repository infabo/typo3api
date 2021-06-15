<?php

namespace Typo3Api\Tca;

use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;

interface TcaConfigurationInterface
{
    public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder);

    public function getColumns(TcaBuilderContext $tcaBuilder): array;

    public function getPalettes(TcaBuilderContext $tcaBuilder): array;

    public function getShowItemString(TcaBuilderContext $tcaBuilder): string;

    public function getDbTableDefinitions(TableBuilderContext $tableBuilder): array;
}
