<?php

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TcaBuilderContext;

class SelectField extends AbstractField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('items');
        $resolver->setAllowedTypes('items', 'array');

        $resolver->setDefaults([
            // values is just a list of possible values
            // you can use it instead of items if you don't want/need to define labels for your options
            'values' => [],
            // items is the normal typo3 compatible item list
            // if not defined, it will be generated from the value list
            'items' => function (Options $options) {
                return array_map(static function ($value) {
                    $label = preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $value);
                    $label = ucfirst(trim(strtolower($label)));
                    return [$label, $value];
                }, $options['values']);
            },
            'itemsProcFunc' => null,

            'required' => true, // TODO i somehow want this to be false now since having an empty option is nice

            'dbType' => function (Options $options) {
                $possibleValues = static::getValuesFromItems($options['items']);
                $defaultValue = addslashes(reset($possibleValues));

                $minimumChars = $options['itemsProcFunc'] ? 30 : 1;
                $maxChars = max($minimumChars, ...array_map('mb_strlen', $possibleValues));

                if ($maxChars > 191) {
                    // Why 191 characters?
                    // Because mysql indexes can only store 767 bytes and I want to enforce a usefull limit.
                    // https://mathiasbynens.be/notes/mysql-utf8mb4#column-index-length
                    // Why are you reading this anyways? Did you really try to select a value that has more than 30 chars?
                    $msg = "The value in an select shouldn't be longer than 191 characters.";
                    $msg .= " The longest value has $maxChars characters.";
                    $msg .= " If you absolutely need to save longer values, define the dbType manually.";
                    throw new InvalidOptionsException($msg);
                }

                return "VARCHAR($maxChars) DEFAULT '$defaultValue' NOT NULL";
            },

            // it doesn't make sense to localize selects (most of the time)
            'localize' => false
        ]);

        $resolver->setAllowedTypes('values', 'array');
        $resolver->setAllowedTypes('items', 'array');
        $resolver->setAllowedTypes('itemsProcFunc', ['null', 'string']);
        $resolver->setAllowedTypes('required', 'bool');

        $resolver->setNormalizer('items', function (Options $options, $items) {
            // ensure at least one value, or an empty value if not required
            if (empty($items) || ($options['required'] === false && $items[0][1] !== '')) {
                array_unshift($items, ['', '']);
            }

            foreach ($items as $value) {
                // the documentation says these chars are invalid
                // https://docs.typo3.org/typo3cms/TCAReference/ColumnsConfig/Type/Select.html#items
                if (preg_match('/[|,;]/', $value[1])) {
                    throw new InvalidOptionsException("The value in an select must not contain the chars '|,;'.");
                }
            }

            return $items;
        });
    }

    private static function getValuesFromItems(array $items): array
    {
        $values = [];

        foreach ($items as $item) {
            if (!isset($item[1])) {
                continue;
            }

            if ($item[1] === '--div--') {
                continue;
            }

            $values[] = $item[1];
        }

        if (empty($values)) {
            $values[] = '';
        }

        return $values;
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        $config = [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => $this->getOption('items'),
        ];

        if ($this->getOption('itemsProcFunc') !== null) {
            $config['itemsProcFunc'] = $this->getOption('itemsProcFunc');
        }

        return $config;
    }
}
