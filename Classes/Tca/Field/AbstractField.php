<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;
use Typo3Api\Exception\TcaFieldException;
use Typo3Api\Tca\TcaConfigurationInterface;

abstract class AbstractField implements TcaConfigurationInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * A cache for option resolvers to speed up duplicate usage.
     * @var array
     */
    private static array $optionResolvers = [];

    /**
     * CommonField constructor.
     * @param string $name
     * @param array $options
     */
    final public function __construct(string $name, array $options = [])
    {
        // Nicer creation syntax when passing name as a direct parameter instead of expecting an option.
        // However: the name must be an option so that it is available during option resolving.
        $options['name'] = $name;

        try {
            $optionResolver = $this->getOptionResolver();
            $this->options = $optionResolver->resolve($options);
        } catch (InvalidOptionsException $e) {
            $this->options = ['name' => $name]; // make getName work for the exception
            throw new TcaFieldException($this, $e->getMessage(), 1508678194, $e);
        }
    }

    private function getOptionResolver()
    {
        if (isset(self::$optionResolvers[get_class($this)])) {
            return self::$optionResolvers[get_class($this)];
        }

        $optionResolver = new OptionsResolver();
        $this->configureOptions($optionResolver);
        self::$optionResolvers[get_class($this)] = $optionResolver;
        return $optionResolver;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'name',
            'dbType'
        ]);
        $resolver->setDefaults([
            'label' => function (Options $options) {
                $splitName = preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $options['name']);
                return ucfirst(strtolower(trim($splitName)));
            },
            'exclude' => false,
            'localize' => true,
            'displayCond' => null,
            'useAsLabel' => false,
            'searchField' => false,
            'useForRecordType' => false,
            'index' => false,
        ]);

        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('label', 'string');
        $resolver->setAllowedTypes('description', 'string');
        $resolver->setAllowedTypes('exclude', 'bool');
        $resolver->setAllowedTypes('dbType', 'string');
        $resolver->setAllowedTypes('localize', 'bool');
        $resolver->setAllowedTypes('displayCond', ['string', 'null']);
        $resolver->setAllowedTypes('useAsLabel', 'bool');
        $resolver->setAllowedTypes('searchField', 'bool');
        $resolver->setAllowedTypes('useForRecordType', 'bool');
        $resolver->setAllowedTypes('index', 'bool');

        /** @noinspection PhpUnusedParameterInspection */
        $resolver->setNormalizer('name', function (Options $options, $name) {
            if (strlen($name) > 64) {
                $msg = "The field name should be at most 64 characters long. (and even that... are you insane?)";
                throw new InvalidOptionsException($msg);
            }

            if (strlen($name) <= 0) {
                $msg = "The field name must not be empty";
                throw new InvalidOptionsException($msg);
            }

            if (strtolower($name) !== $name) {
                $msg = "The field name must be lower case.";
                throw new InvalidOptionsException($msg);
            }

            if (!preg_match('#^\w*$#', $name)) {
                $msg = "The field name should only contain word characters to avoid potential problems.";
                throw new InvalidOptionsException($msg);
            }

            return $name;
        });
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption(string $name)
    {
        return $this->options[$name];
    }

    public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder)
    {
        $fieldName = $this->getOption('name');

        if ($this->getOption('useAsLabel')) {
            if (!isset($ctrl['label']) || $ctrl['label'] === 'uid') {
                $ctrl['label'] = $fieldName;
            } else {
                if (!isset($ctrl['label_alt'])) {
                    $ctrl['label_alt'] = $fieldName;
                } elseif (!str_contains($ctrl['label_alt'], $fieldName)) {
                    $ctrl['label_alt'] .= ', ' . $fieldName;
                }
            }
        }

        if ($this->getOption('searchField')) {
            if (!isset($ctrl['searchFields'])) {
                $ctrl['searchFields'] = $fieldName;
            } elseif (!str_contains($ctrl['searchFields'], $fieldName)) {
                $ctrl['searchFields'] .= ', ' . $fieldName;
            }
        }

        if ($this->getOption('useForRecordType')) {
            if (isset($ctrl['type'])) {
                $msg = "Only one field can specify the record type for table $tcaBuilder.";
                $msg .= " Tried using field " . $fieldName . " as type field.";
                $msg .= " Field " . $ctrl['type'] . " is already defined as type field.";
                throw new \RuntimeException($msg);
            }

            $ctrl['type'] = $fieldName;
        }
    }

    public function getColumns(TcaBuilderContext $tcaBuilder): array
    {
        $column = [
            'label' => $this->getOption('label'),
            'config' => $this->getFieldTcaConfig($tcaBuilder),
        ];

        if ($this->getOption('description')) {
            $column['description'] = $this->getOption('description');
        }

        if ($this->getOption('exclude')) {
            $column['exclude'] = true;
        }

        if ($this->getOption('localize') === false) {
            $column['l10n_mode'] = 'exclude';
            $column['l10n_display'] = 'defaultAsReadonly';
        }

        if ($this->getOption('displayCond') !== null) {
            $column['displayCond'] = $this->getOption('displayCond');
        }

        return [
            $this->getOption('name') => $column
        ];
    }

    public function getPalettes(TcaBuilderContext $tcaBuilder): array
    {
        return [];
    }

    abstract public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder);

    public function getDbTableDefinitions(TableBuilderContext $tableBuilder): array
    {
        $name = addslashes($this->getOption('name'));
        $definition = [$tableBuilder->getTableName() => ["`$name` " . $this->getOption('dbType')]];

        if ($this->getOption('index')) {
            // TODO I'd really like multi field indexes that are somehow nameable
            $definition[$tableBuilder->getTableName()][] = "INDEX `$name`(`$name`)";
        }

        return $definition;
    }

    public function getShowItemString(TcaBuilderContext $tcaBuilder): string
    {
        return $this->getOption('name');
    }

    public function getName(): string
    {
        return $this->getOption('name');
    }
}
