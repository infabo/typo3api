<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TcaBuilderContext;

class ValueSlider extends IntField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'step' => 10,
            'width' => 200,
        ]);

        $resolver->setAllowedTypes('step', 'int');
        $resolver->setAllowedTypes('width', 'int');
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        $config = parent::getFieldTcaConfig($tcaBuilder);
        $config['slider'] = [
            'lower' => $this->getOption('step'),
            'upper' => $this->getOption('width')
        ];
        return $config;
    }
}
