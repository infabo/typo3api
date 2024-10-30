<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Typo3Api\Builder\Context\TcaBuilderContext;
use Typo3Api\Utility\DbFieldDefinition;

class FileField extends AbstractField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'allowedFileExtensions' => '',
            'disallowedFileExtensions' => '', // only makes sense if allowedFileExtensions is empty
            'minitems' => 0,
            'maxitems' => 100,
            'collapseAll' => true,
            'allowHide' => fn(Options $options) => // if you define minitems, you'd expect there to be at least one item.
// however: hiding elements will prevent this so i just decided to disable hiding by default then.
$options['minitems'] === 0,
            'dbType' => fn(Options $options) => DbFieldDefinition::getIntForNumberRange(0, $options['maxitems']),
        ]);

        $resolver->setAllowedTypes('allowedFileExtensions', ['string', 'array']);
        $resolver->setAllowedTypes('disallowedFileExtensions', ['string', 'array']);

        $resolver->setAllowedTypes('minitems', 'int');
        $resolver->setAllowedTypes('maxitems', 'int');
        $resolver->setAllowedTypes('allowHide', 'bool');

        $normalizeFileExtensions = function (Options $options, $fileExtensions) {
            if (is_string($fileExtensions)) {
                $fileExtensions = GeneralUtility::trimExplode(',', $fileExtensions);
            }

            /**
             * @phpstan-ignore-next-line
             */
            return implode(',', array_filter($fileExtensions, 'strlen'));
        };
        $resolver->setNormalizer('allowedFileExtensions', $normalizeFileExtensions);
        $resolver->setNormalizer('disallowedFileExtensions', $normalizeFileExtensions);

        /** @noinspection PhpUnusedParameterInspection */
        $resolver->setNormalizer('minitems', function (Options $options, $minitems) {
            if ($minitems < 0) {
                throw new InvalidOptionsException("minitems must not be smaller than 0");
            }

            return $minitems;
        });

        $resolver->setNormalizer('maxitems', function (Options $options, $maxitems) {
            if ($maxitems < $options['minitems']) {
                throw new InvalidOptionsException("maxitems must not be smaller than minitems");
            }

            return $maxitems;
        });
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        return [
            'type' => 'file',
            'minitems' => $this->getOption('minitems'),
            'maxitems' => $this->getOption('maxitems'),
            'allowed' => $this->getOption('allowedFileExtensions'),
            'disallowed' => $this->getOption('disallowedFileExtensions'),
            'appearance' => [
                'collapseAll' => $this->getOption('collapseAll'),
                'showPossibleLocalizationRecords' => $this->getOption('localize'),
                'showAllLocalizationLink' => $this->getOption('localize'),
                'showSynchronizationLink' => $this->getOption('localize'),
                'enabledControls' => [
                    'hide' => $this->getOption('allowHide'),
                    'localize' => $this->getOption('localize'),
                ]
            ]
        ];
    }
}
