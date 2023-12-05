<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TcaBuilderContext;

class RteField extends AbstractField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'dbType' => "MEDIUMTEXT DEFAULT NULL",
            'richtextConfiguration' => null,
        ]);

        $resolver->setAllowedTypes('richtextConfiguration', ['string', 'null']);
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        $tcaConfig = [
            'type' => 'text',

            // rows and cols are ignored anyways unless rte is ignored
            'cols' => '80',
            'rows' => '15',

            'softref' => 'typolink_tag,images,email[subst],url',
            'enableRichtext' => true,
        ];

        if ($this->getOption('richtextConfiguration')) {
            $tcaConfig['richtextConfiguration'] = $this->getOption('richtextConfiguration');
        }

        return $tcaConfig;
    }
}
