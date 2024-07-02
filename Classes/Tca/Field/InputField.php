<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TcaBuilderContext;

class InputField extends AbstractField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            // normally devs put 255 as the default size for inputs because it's the common "best size" for varchar
            // however, in my experience, nobody expects an input to be filled with so much text
            // also: the limit of 255 feels random for a normal human
            // that's why i use 50 as a default
            'max' => 50,
            'size' => fn(Options $options) => $options['max'],
            'default' => '',
            'placeholder' => null,
            'trim' => true,
            'charset' => null,
            'is_in' => null,
            'case' => 'any',
            'nospace' => false,
            'unique' => false,

            'dbType' => function (Options $options) {
                $maxCharacters = $options['max'];
                if ($maxCharacters > 1024) {
                    $msg = "The max size of an input field should not exeed 1024 characters.";
                    $msg .= " Use a textarea for saving that many characters.";
                    $msg .= " If you must use an input field for some reason:";
                    $msg .= " define the dbType option with a fitting db type option like TEXT.";
                    throw new InvalidOptionsException($msg);
                }

                $default = addslashes((string) $options['default']);
                return "VARCHAR($maxCharacters) DEFAULT '$default' NOT NULL";
            // using anything but varchar here would make searchFields slow.
                // I opted to prevent large input fields and by default add everything to searchFields
                // also, i can easily use the default option here which is nice.
            },
            'index' => fn(Options $options) => $options['unique'],
            'useAsLabel' => true,
            'searchField' => true,
        ]);

        $resolver->setAllowedTypes('max', 'int');
        $resolver->setAllowedTypes('size', 'int');
        $resolver->setAllowedTypes('default', 'string');
        $resolver->setAllowedTypes('placeholder', ['null', 'string']);
        $resolver->setAllowedTypes('trim', 'bool');
        $resolver->setAllowedValues('charset', [null, 'alpha', 'alphanum', 'alphanum_x']);
        $resolver->setAllowedTypes('is_in', ['null', 'string']);
        $resolver->setAllowedValues('case', ['lower', 'upper', 'any']);
        $resolver->setAllowedTypes('nospace', 'bool');
        $resolver->setAllowedTypes('unique', 'bool');

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
            'type' => 'input',
            'size' => (int)($this->getOption('size') / 2), // adjust the size to fit the character count better
            'max' => $this->getOption('max'),
            'eval' => implode(',', $this->getEvals()),
            'default' => $this->getOption('default'),
        ];

        if ($this->getOption('placeholder') !== null) {
            $config['placeholder'] = $this->getOption('placeholder');
        }

        if ($this->getOption('is_in')) {
            $config['is_in'] = $this->getOption('is_in');
        }

        return $config;
    }

    /**
     * @return array
     */
    protected function getEvals(): array
    {
        $evals = [];

        if ($this->getOption('trim')) {
            $evals[] = 'trim';
        }

        if ($this->getOption('charset')) {
            $evals[] = $this->getOption('charset');
        }

        if ($this->getOption('is_in')) {
            $evals[] = 'is_in';
        }

        if ($this->getOption('case') !== 'any') {
            $evals[] = $this->getOption('case');
        }

        if ($this->getOption('nospace')) {
            $evals[] = 'nospace';
        }

        if ($this->getOption('unique')) {
            // i decided against the "real" unique and prefer to always use uniqueInPid
            // it's like a name in a filesystem, you expect it to be unique in a folder
            // there is no "real" unique in typo3 anyways because of deleted and versioning
            $evals[] = 'uniqueInPid';
        }

        return $evals;
    }
}
