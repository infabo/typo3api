<?php

namespace Typo3Api\Tca\Field;


use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;
use Typo3Api\Builder\TableBuilder;
use Typo3Api\Utility\ForeignTableUtility;


class MultiSelectRelationField extends AbstractField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired([
            'foreign_table'
        ]);

        $resolver->setDefaults([
            'foreign_table_where' => '',
            'items' => [], // TODO test this
            'minitems' => 0,
            'maxitems' => 100,
            'size' => 7,
            'enableSearch' => true,

            'dbType' => "INT(11) DEFAULT '0' NOT NULL",
            'localize' => false,
        ]);

        $resolver->setAllowedTypes('foreign_table', ['string', TableBuilder::class]);
        $resolver->setAllowedTypes('foreign_table_where', 'string');
        $resolver->setAllowedTypes('items', 'array');

        /** @noinspection PhpUnusedParameterInspection */
        $resolver->setNormalizer('foreign_table', function (Options $options, $foreignTable) {
            if ($foreignTable instanceof TableBuilder) {
                return $foreignTable->getTableName();
            }

            return $foreignTable;
        });

        $resolver->setNormalizer('foreign_table_where', function (Options $options, string $where) {
            return ForeignTableUtility::normalizeForeignTableWhere($options['foreign_table'], $where);
        });

        $resolver->setNormalizer('minitems', function (Options $options, $minItems) {
            if ($minItems < 0) {
                throw new InvalidOptionsException("Minitems can't be smaller than 0, got $minItems.");
            }

            if (
                $minItems > 0
                && isset($GLOBALS['TCA'][$options['foreign_table']]['ctrl']['enablecolumns'])
                && !empty($GLOBALS['TCA'][$options['foreign_table']]['ctrl']['enablecolumns'])
            ) {
                $msg = "minitems can't be used if the foreign_table has enablecolumns. This is to prevent unexpected behavior.";
                $msg .= " Someone could create a relation and disable the related record (eg. by setting endtime).";
                $msg .= " Typo3 can't catch that so it is better to just not use minitems in combination with enablecolumns.";
                throw new InvalidOptionsException($msg);
            }

            return $minItems;
        });
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        if (!$tcaBuilder instanceof TableBuilderContext) {
            $type = get_debug_type($tcaBuilder);
            throw new \RuntimeException("Expected " . TableBuilderContext::class . ", got $type");
        }

        return [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => $this->getOption('foreign_table'),
            'foreign_table_where' => $this->getOption('foreign_table_where'),
            'MM' => $this->getMnTableName($tcaBuilder),
            'items' => $this->getOption('items'),
            'size' => $this->getOption('size'),
            'minitems' => $this->getOption('minitems'),
            'maxitems' => $this->getOption('maxitems'),
            'enableMultiSelectFilterTextfield' => $this->getOption('enableSearch'),
        ];
    }

    public function getColumns(TcaBuilderContext $tcaBuilder): array
    {
        $columns = parent::getColumns($tcaBuilder);

        if ($this->getOption('localize') === false) {
            // remove the l10n display options
            // selectMultipleSideBySide cant be displayed as readonly
            unset($columns[$this->getOption('name')]['l10n_display']);
        }

        return $columns;
    }

    public function getDbTableDefinitions(TableBuilderContext $tableBuilder): array
    {
        $dbTableDefinitions = parent::getDbTableDefinitions($tableBuilder);

        $dbTableDefinitions[$this->getMnTableName($tableBuilder)] = [
            "uid_local int(11) DEFAULT '0' NOT NULL",
            "uid_foreign int(11) DEFAULT '0' NOT NULL",
            "sorting int(11) DEFAULT '0' NOT NULL",

            // every table should have a primary key
            // todo find reference for this
            // by defining the foreign key first, mysql can use this key for reverse relations
            // while the local key (below) can be used for the correct direction + potential sorting
            "PRIMARY KEY (uid_foreign, uid_local)",

            // use index for access
            // also index the sorting field
            // https://dba.stackexchange.com/a/11042
            "INDEX local (uid_local, sorting ASC)",
        ];

        return $dbTableDefinitions;
    }

    /**
     * @param TableBuilderContext $tableBuilder
     *
     * @return string
     */
    protected function getMnTableName(TableBuilderContext $tableBuilder): string
    {
        return $tableBuilder->getTableName() . '_' . $this->getOption('name') . '_mm';
    }

}
