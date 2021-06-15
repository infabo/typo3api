<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;

/**
 * This simple configuration just passes though the showitem string passed during construct.
 * It can be used to easily reuse fields that have already been defined (for example while creating a new type).
 */
class ShowitemConfiguration implements TcaConfigurationInterface
{
    /**
     * @var string
     */
    private $showitem;

    /**
     * @param string|array $showitem
     */
    public function __construct($showitem)
    {
        if (is_array($showitem)) {
            $showitem = implode(', ', $showitem);
        }

        if (!is_string($showitem)) {
            $type = get_debug_type($showitem);
            throw new \RuntimeException("Expected showitem to be a string or an array, got $type.");
        }

        $this->showitem = $showitem;
    }

    public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder)
    {
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
        return $this->showitem;
    }

    public function getDbTableDefinitions(TableBuilderContext $tableBuilder): array
    {
        return [];
    }
}
