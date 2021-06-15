<?php

namespace Typo3Api\Tca\Field;


use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $evals[] = 'email';
        return $evals;
    }

}