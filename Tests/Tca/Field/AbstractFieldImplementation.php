<?php

namespace Typo3Api\Tca\Field;


use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TcaBuilderContext;

class AbstractFieldImplementation extends AbstractField
{
    public function getFieldTcaConfig(TcaBuilderContext $tcaContext)
    {
        return [];
    }

    #[\Override]
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'dbType' => AbstractFieldTest::STUB_DB_TYPE
        ]);
    }

}