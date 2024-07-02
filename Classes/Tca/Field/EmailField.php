<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TcaBuilderContext;

class EmailField extends InputField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'max' => 100,
            'size' => 40, // https://stackoverflow.com/a/1297352/1973256
            'localize' => false
        ]);
    }

    protected function getEvals(): array
    {
        $evals = parent::getEvals();

        return $evals;
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        $config = parent::getFieldTcaConfig($tcaBuilder);
        $config['type'] = 'email';

        return $config;
    }
}
