<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TcaBuilderContext;

class CheckboxField extends AbstractField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'checkbox_label' => 'Enabled',
            'default' => false,
            'dbType' => function (Options $options) {
                $default = $options['default'] ? '1' : '0';
                return "TINYINT(1) DEFAULT '$default' NOT NULL";
            },
            'localize' => false
        ]);

        $resolver->setAllowedTypes('checkbox_label', 'string');
        $resolver->setAllowedTypes('default', 'bool');
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        return [
            'type' => 'check',
            'default' => $this->getOption('default'),
            'items' => [
                [$this->getOption('checkbox_label'), '']
            ]
        ];
    }
}
