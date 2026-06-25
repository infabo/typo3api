<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TcaBuilderContext;

/**
 * A field for url path segments (slugs).
 *
 * The value is generated from one or more other fields (defined via the "fields" option)
 * and sanitized to be safe for usage in urls.
 *
 * @see https://docs.typo3.org/m/typo3/reference-tca/14.3/en-us/ColumnsConfig/Type/Slug/Index.html
 */
class SlugField extends AbstractField
{
    #[\Override]
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            // the field(s) the slug is generated from.
            // can be a single field name, a list of field names or a list of fallback groups,
            // e.g. 'title', ['title'] or [['nav_title', 'title']]
            'fields' => [],

            // generator options
            'fieldSeparator' => '/',
            'prefixParentPageSlug' => false,
            'replacements' => [],
            'regexReplacements' => [],
            'postModifiers' => [],

            // the character used to replace forbidden characters
            'fallbackCharacter' => '-',

            // whether the slug should start with a slash
            'prependSlash' => false,

            // uniqueness validation: 'unique', 'uniqueInSite', 'uniqueInPid' or '' to disable
            'eval' => 'uniqueInSite',

            // a userFunc returning the prefix shown in front of the slug in the backend
            'appearancePrefix' => null,

            'size' => 50,
            'default' => '',
            'required' => false,
            'readOnly' => false,

            // the maximum number of characters stored in the database
            'max' => 2048,

            'dbType' => function (Options $options) {
                $default = addslashes((string)$options['default']);
                return "VARCHAR({$options['max']}) DEFAULT '$default' NOT NULL";
            },

            // a slug isn't really a label and shouldn't be searched by default
            'localize' => true,
            'useAsLabel' => false,
            'searchField' => false,
        ]);

        $resolver->setAllowedTypes('fields', ['string', 'array']);
        $resolver->setAllowedTypes('fieldSeparator', 'string');
        $resolver->setAllowedTypes('prefixParentPageSlug', 'bool');
        $resolver->setAllowedTypes('replacements', 'array');
        $resolver->setAllowedTypes('regexReplacements', 'array');
        $resolver->setAllowedTypes('postModifiers', 'array');
        $resolver->setAllowedTypes('fallbackCharacter', 'string');
        $resolver->setAllowedTypes('prependSlash', 'bool');
        $resolver->setAllowedValues('eval', ['', 'unique', 'uniqueInSite', 'uniqueInPid']);
        $resolver->setAllowedTypes('appearancePrefix', ['null', 'string']);
        $resolver->setAllowedTypes('size', 'int');
        $resolver->setAllowedTypes('default', 'string');
        $resolver->setAllowedTypes('required', 'bool');
        $resolver->setAllowedTypes('readOnly', 'bool');
        $resolver->setAllowedTypes('max', 'int');

        // accept a single field name as a convenient shorthand and normalize it to a list
        /** @noinspection PhpUnusedParameterInspection */
        $resolver->setNormalizer('fields', function (Options $options, $fields) {
            if (is_string($fields)) {
                $fields = [$fields];
            }

            if ($fields === []) {
                $msg = "A slug field needs at least one source field to generate its value from.";
                $msg .= " Define the 'fields' option, e.g. ['title'].";
                throw new InvalidOptionsException($msg, 1_750_000_000);
            }

            return $fields;
        });
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        $generatorOptions = [
            'fields' => $this->getOption('fields'),
            'fieldSeparator' => $this->getOption('fieldSeparator'),
        ];

        if ($this->getOption('prefixParentPageSlug')) {
            $generatorOptions['prefixParentPageSlug'] = true;
        }

        if ($this->getOption('replacements')) {
            $generatorOptions['replacements'] = $this->getOption('replacements');
        }

        if ($this->getOption('regexReplacements')) {
            $generatorOptions['regexReplacements'] = $this->getOption('regexReplacements');
        }

        if ($this->getOption('postModifiers')) {
            $generatorOptions['postModifiers'] = $this->getOption('postModifiers');
        }

        $config = [
            'type' => 'slug',
            'size' => $this->getOption('size'),
            'generatorOptions' => $generatorOptions,
            'fallbackCharacter' => $this->getOption('fallbackCharacter'),
            'prependSlash' => $this->getOption('prependSlash'),
            'default' => $this->getOption('default'),
        ];

        if ($this->getOption('eval')) {
            $config['eval'] = $this->getOption('eval');
        }

        if ($this->getOption('required')) {
            $config['required'] = true;
        }

        if ($this->getOption('readOnly')) {
            $config['readOnly'] = true;
        }

        if ($this->getOption('appearancePrefix') !== null) {
            $config['appearance'] = ['prefix' => $this->getOption('appearancePrefix')];
        }

        return $config;
    }
}
