<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;

class CustomConfiguration implements TcaConfigurationInterface
{
    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        // TODO cache option resolver creation
        $resolver = new OptionsResolver();

        $resolver->setDefaults([
            'ctrl' => [],
            'columns' => [],
            'palettes' => [],
            'showitem' => '',
            'dbTableDefinition' => []
        ]);

        $resolver->setAllowedTypes('ctrl', 'array');
        $resolver->setAllowedTypes('columns', 'array');
        $resolver->setAllowedTypes('palettes', 'array');
        $resolver->setAllowedTypes('showitem', ['array', 'string']);
        $resolver->setAllowedTypes('dbTableDefinition', 'array');

        /** @noinspection PhpUnusedParameterInspection */
        $resolver->setNormalizer('showitem', function (Options $options, $showitem) {
            if (is_array($showitem)) {
                $showitem = implode(', ', array_filter($showitem));
            }

            return $showitem;
        });

        $this->options = $resolver->resolve($options);
    }

    public function getOption(string $option)
    {
        return $this->options[$option];
    }

    public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder)
    {
        foreach ($this->getOption('ctrl') as $key => $value) {
            $ctrl[$key] = $value;
        }
    }

    public function getColumns(TcaBuilderContext $tcaBuilder): array
    {
        return $this->getOption('columns');
    }

    public function getPalettes(TcaBuilderContext $tcaBuilder): array
    {
        return $this->getOption('palettes');
    }

    public function getShowItemString(TcaBuilderContext $tcaBuilder): string
    {
        return $this->getOption('showitem');
    }

    public function getDbTableDefinitions(TableBuilderContext $tableBuilder): array
    {
        return $this->getOption('dbTableDefinition');
    }
}
