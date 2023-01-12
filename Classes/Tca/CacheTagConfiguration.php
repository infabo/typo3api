<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;

/**
 * Class CacheTagConfiguration
 *
 * ->configure(mew \Typo3Api\Tca\CacheTagConfiguration('tx_myextension'))
 * ->configure(mew \Typo3Api\Tca\CacheTagConfiguration('tx_myextension_table_####uid###'))
 */
class CacheTagConfiguration implements TcaConfigurationInterface
{
    public function __construct(private string $tag, private string $group = 'pages')
    {
    }

    public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder)
    {
        $ctrl['EXT']['typo3api']['cache_tags'][$this->group][$this->tag] = $this->tag;
    }

    public function getColumns(TcaBuilderContext $tcaBuilder): array
    {
        return [];
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

    public function getTag(): string
    {
        return $this->tag;
    }
}
