<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;
use Typo3Api\Builder\TableBuilder;
use Typo3Api\Utility\DbFieldDefinition;

class InlineRelationField extends AbstractField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('foreign_table');
        $resolver->setDefaults([
            'foreign_field' => 'parent_uid',
            // if foreignTakeover is true, the other table is exclusive for this relation (recommended)
            // this means hideTable will be set to true, and some other behaviors will change
            // however: you can still use the foreign table for other inline relations
            'foreignTakeover' => true,
            'minitems' => 0,
            'maxitems' => 100, // at some point, inline record editing doesn't make sense anymore
            'collapseAll' => fn(Options $options) => $options['maxitems'] > 5,

            'dbType' => fn(Options $options) => DbFieldDefinition::getIntForNumberRange(0, $options['maxitems']),
        ]);

        $resolver->setAllowedTypes('foreign_table', ['string', TableBuilder::class]);
        $resolver->setAllowedTypes('foreign_field', 'string');
        $resolver->setAllowedTypes('minitems', 'int');
        $resolver->setAllowedTypes('maxitems', 'int');

        /** @noinspection PhpUnusedParameterInspection */
        $resolver->setNormalizer('foreign_table', function (Options $options, $foreignTable) {
            if ($foreignTable instanceof TableBuilder) {
                return $foreignTable->getTableName();
            }

            return $foreignTable;
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

    public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder)
    {
        parent::modifyCtrl($ctrl, $tcaBuilder);

        $foreignTable = $this->getOption('foreign_table');
        if (!isset($GLOBALS['TCA'][$foreignTable])) {
            $msg = "Configure $foreignTable before adding it in the irre configuraiton of $tcaBuilder.";
            $msg .= "\nThis can also be a loading order issue of tca files. You can try to put the inline relation into TCA/Overrides.";
            $msg .= "\nIf you just need the foreign table in this relation, you might also consider configuring it inline here.";
            throw new \RuntimeException($msg);
        }

        $foreignTableDefinition = $GLOBALS['TCA'][$foreignTable];
        $sortby = @$foreignTableDefinition['ctrl']['sortby'] ?: @$foreignTableDefinition['ctrl']['_sortby'];

        if ($this->getOption('foreignTakeover')) {
            // the doc states that sortby should be disabled if the table is exclusive for this relation
            // https://docs.typo3.org/typo3cms/TCAReference/8-dev/ColumnsConfig/Type/Inline.html#foreign-sortby
            if ($sortby) {
                $GLOBALS['TCA'][$foreignTable]['ctrl']['sortby'] = null;
                $GLOBALS['TCA'][$foreignTable]['ctrl']['_sortby'] = $sortby;
            }

            // ensure only this relation sees the other table
            $GLOBALS['TCA'][$foreignTable]['ctrl']['hideTable'] = true;

            // since this table can't normally be created anymore, remove creation restrictions
            // ExtensionManagementUtility::allowTableOnStandardPages($foreignTable);
            $GLOBALS['TCA']['pages']['ctrl']['EXT']['typo3api']['allow_tables'][] = $foreignTable;
        }
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        $foreignTable = $this->getOption('foreign_table');
        if (!isset($GLOBALS['TCA'][$foreignTable])) {
            throw new \RuntimeException("Configure $foreignTable before adding it in the irre configuraiton of $tcaBuilder");
        }

        $foreignTableDefinition = $GLOBALS['TCA'][$foreignTable];
        $sortby = @$foreignTableDefinition['ctrl']['sortby'] ?: @$foreignTableDefinition['ctrl']['_sortby'];
        $canBeSorted = (bool)$sortby;
        $canLocalize = (bool)@$foreignTableDefinition['ctrl']['languageField'];
        $canHide = (bool)@$foreignTableDefinition['columns']['hidden'];

        return [
            'type' => 'inline',
            'foreign_table' => $this->getOption('foreign_table'),
            'foreign_field' => $this->getOption('foreign_field'),
            'foreign_sortby' => $sortby,
            'minitems' => $this->getOption('minitems'),
            'maxitems' => $this->getOption('maxitems'),
            'behaviour' => [
                'enableCascadingDelete' => $this->getOption('foreignTakeover'),
            ],
            'appearance' => [
                'collapseAll' => $this->getOption('collapseAll') ? 1 : 0,
                'useSortable' => $canBeSorted,
                'showPossibleLocalizationRecords' => $canLocalize,
                'showAllLocalizationLink' => $canLocalize,
                'showSynchronizationLink' => $canLocalize, // potentially dangerous...
                'enabledControls' => [
                    'info' => true,
                    'new' => true,
                    'dragdrop' => $canBeSorted,
                    'sort' => $canBeSorted,
                    'hide' => $canHide,
                    'delete' => true,
                    'localize' => $canLocalize,
                ],
            ],
        ];
    }

    public function getColumns(TcaBuilderContext $tcaBuilder): array
    {
        $columns = parent::getColumns($tcaBuilder);

        if ($this->getOption('localize') === false) {
            // remove the l10n display options
            // inline field cant be displayed as readonly
            unset($columns[$this->getOption('name')]['l10n_display']);
        }

        return $columns;
    }

    public function getDbTableDefinitions(TableBuilderContext $tableBuilder): array
    {
        $tableDefinitions = parent::getDbTableDefinitions($tableBuilder);

        // define the field on the other side
        // TODO somewhere it should be checked if this field is already defined
        $foreignField = addslashes($this->getOption('foreign_field'));
        $foreignTable = $this->getOption('foreign_table');

        // for self referencing relations the foreign table key might already exist, otherwise create it
        if (!array_key_exists($foreignTable, $tableDefinitions)) {
            $tableDefinitions[$foreignTable] = [];
        }

        $tableDefinitions[$foreignTable][] = "`$foreignField` INT(11) DEFAULT '0' NOT NULL";
        $tableDefinitions[$foreignTable][] = "KEY `$foreignField`(`$foreignField`)";

        $foreignTable = $this->getOption('foreign_table');
        $foreignTableDefinition = $GLOBALS['TCA'][$foreignTable];
        if(@$foreignTableDefinition['ctrl']['sortby'] !== null) {
            // sorting is always local to the pid so putting that in the index might help a lot
            $tableDefinitions[$foreignTable][] = "INDEX sorting (pid, {$foreignTableDefinition['ctrl']['sortby']})";
        }
        if(@$foreignTableDefinition['ctrl']['_sortby'] !== null) {
            // sorting is always local to the pid so putting that in the index might help a lot
            $tableDefinitions[$foreignTable][] = "INDEX sorting_alt (pid, {$foreignTableDefinition['ctrl']['_sortby']})";
        }

        return $tableDefinitions;
    }
}
