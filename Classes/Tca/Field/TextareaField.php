<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TcaBuilderContext;

class TextareaField extends AbstractField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'max' => 500,
            'cols' => 30,
            'rows' => function (Options $options) {
                $realCols = $options['cols'] * 2; // cols doesn't cut it exactly so adjust a bit.
                $calculatedSpace = floor($options['max'] / $realCols);
                return min((int)$calculatedSpace, 10);
            },
            'placeholder' => null,
            'required' => false,
            'trim' => true,
            'dbType' => function (Options $options) {
                $maxChars = $options['max'];

                // the reason i don't use tinytext is that in typo3, most of the time, every column is requested
                // getting a column that is stored outside the table has a performance impact
                // therefor varchar should generally perform better
                // however, storing everything in varchar might break the limit of 65535 bytes per row
                // i consider 1024 a nice limit for a varchar which (because of utf8mb4) can grow up to 4096 bytes
                if ($maxChars <= 1024) {
                    // text types don't have a default value and therefor default to null
                    // because of this, I make the varchar version also default to null for consistent behavior
                    return "VARCHAR($maxChars) DEFAULT NULL";
                }

                // the mysql text field can save up to 65535 bytes (not characters)
                // 1 character can have up to 4 bytes since I want to support utf8mb4
                // however... typo3 doesn't support utf8mb4 yet: https://forge.typo3.org/issues/80398
                // but it's nice to be prepared
                $maxBytes = $maxChars * 4;

                if ($maxBytes < 1 << 16) {
                    return "TEXT DEFAULT NULL";
                }

                if ($maxBytes < 1 << 24) {
                    return "MEDIUMTEXT DEFAULT NULL";
                }

                $msg = "Tried to store a text field with up to $maxBytes bytes ($maxChars characters).";
                $msg .= " This can't be stored in a MEDIUMTEXT and LONGTEXT might get to big for a php process.";
                $msg .= " Even if you increase the memory limit, the translation system of typo3 uses MEDIUMTEXT too.";
                $msg .= " Try to use a sensible character limit or store your data in a file if possible.";
                throw new InvalidOptionsException($msg);
            },
        ]);

        $resolver->setAllowedTypes('max', 'int');
        $resolver->setAllowedTypes('cols', 'int');
        $resolver->setAllowedTypes('rows', 'int');
        $resolver->setAllowedTypes('placeholder', ['null', 'string']);
        $resolver->setAllowedTypes('required', 'bool');
        $resolver->setAllowedTypes('trim', 'bool');

        /** @noinspection PhpUnusedParameterInspection */
        $resolver->setNormalizer('max', function (Options $options, $maxLength) {
            if ($maxLength < 1) {
                $msg = "Max size of input can't be smaller than 1, got $maxLength";
                throw new InvalidOptionsException($msg);
            }
            return $maxLength;
        });
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        $config = [
            'type' => 'text',
            'max' => $this->getOption('max'),
            'rows' => $this->getOption('rows'),
            'eval' => implode(',', array_filter([
                $this->getOption('trim') ? 'trim' : null,
                $this->getOption('required') ? 'required' : null,
                // i'd love to define null here, but this will render a checkbox which i don't want
            ])),
        ];

        if ($this->getOption('placeholder') !== null) {
            $config['placeholder'] = $this->getOption('placeholder');
        }

        return $config;
    }
}
