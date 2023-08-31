<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

use Typo3Api\Builder\Context\TcaBuilderContext;

class NamedPalette extends CompoundTcaConfiguration
{
    /**
     * @var string
     */
    private readonly string $name;

    public function __construct(string $name, array $children = [])
    {
        if (preg_match('/[,;]/', $name)) {
            throw new \RuntimeException("The name of a palette must not contain comma or semicolon, got $name");
        }

        parent::__construct($children);
        $this->name = $name;
    }

    public function getPaletteName(TcaBuilderContext $tcaBuilder)
    {
        return preg_replace('/\W+/', '_', parent::getShowItemString($tcaBuilder));
    }

    public function getPalettes(TcaBuilderContext $tcaBuilder): array
    {
        $showItems = [];

        foreach ($this->children as $child) {
            $showItems[] = $child->getShowItemString($tcaBuilder);
        }

        $palettes = [
            $this->getPaletteName($tcaBuilder) => [
                'showitem' => implode(', ', array_filter($showItems))
            ]
        ];

        foreach ($this->children as $child) {
            foreach ($child->getPalettes($tcaBuilder) as $paletteName => $paletteDefinition) {
                $palettes[$paletteName] = $paletteDefinition;
            }
        }

        return $palettes;
    }

    public function getShowItemString(TcaBuilderContext $tcaBuilder): string
    {
        return "--palette--; {$this->name}; " . $this->getPaletteName($tcaBuilder);
    }
}
