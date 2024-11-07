<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TcaBuilderContext;
use Typo3Api\Utility\DbFieldDefinition;

class IntField extends AbstractField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'min' => 0,
            'max' => 1_000_000, // default up to a million
            'size' => fn(Options $options) => max(strlen((string)$options['min']), strlen((string)$options['max'])),
            'default' => fn(Options $options) => // try to get default as close to 0 as possible
max($options['min'], min($options['max'], 0)),
            'required' => false, // TODO required is kind of useless on an int since the backend doesn't allow en empty value

            'dbType' => function (Options $options) {
                $low = $options['min'];
                $high = $options['max'];
                $default = $options['default'];
                return DbFieldDefinition::getIntForNumberRange($low, $high, $default);
            },
            // an int field is most of the time not required to be localized
            'localize' => false,
        ]);

        $resolver->setAllowedTypes('min', 'int');
        $resolver->setAllowedTypes('max', 'int');
        $resolver->setAllowedTypes('size', 'int');
        $resolver->setAllowedTypes('default', 'int');
        $resolver->setAllowedTypes('required', 'bool');
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        return [
            'type' => 'number',
            'size' => (int)($this->getOption('size') / 2), // adjust the size to fit the character count better
            'default' => $this->getOption('default'),
            'range' => [
                'lower' => $this->getOption('min'),
                'upper' => $this->getOption('max')
            ],
            'required' => $this->getOption('required'),
        ];
    }
}
