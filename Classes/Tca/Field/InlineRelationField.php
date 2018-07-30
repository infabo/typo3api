<?php

namespace Typo3Api\Tca\Field;


use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
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
            'collapseAll' => function (Options $options) {
                return $options['maxitems'] > 5;
            },

            'dbType' => function (Options $options) {
                return DbFieldDefinition::getIntForNumberRange(0, $options['maxitems']);
            },
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
        if (!$tcaBuilder instanceof TableBuilderContext) {
            $type = is_object($tcaBuilder) ? get_class($tcaBuilder) : gettype($tcaBuilder);
            $msg = "Inline Relation is only supported on database tables";
            $msg .= " so the context must be a " . TableBuilderContext::class . ", got $type";
            throw new \RuntimeException($msg);
        }

        parent::modifyCtrl($ctrl, $tcaBuilder);

        if ($this->getOption('foreignTakeover')) {
            $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
            $dispatcher->connect(ExtensionManagementUtility::class, 'tcaIsBeingBuilt', function ($tca) use ($tcaBuilder) {
                $foreignTable = $this->getOption('foreign_table');
                if (!isset($tca[$foreignTable])) {
                    throw new \RuntimeException("$foreignTable not defined. Used in inline relation for $tcaBuilder");
                }

                $sortby = @$tca[$foreignTable]['ctrl']['sortby'] ?: @$tca[$foreignTable]['ctrl']['_sortby'];

                // the doc states that sortby should be disabled if the table is exclusive for this relation
                // https://docs.typo3.org/typo3cms/TCAReference/8.7/ColumnsConfig/Type/Inline.html#foreign-sortby
                if ($sortby) {
                    $tca[$foreignTable]['ctrl']['sortby'] = null;
                    $tca[$foreignTable]['ctrl']['_sortby'] = $sortby;
                }

                // ensure only this relation sees the other table
                $tca[$foreignTable]['ctrl']['hideTable'] = true;

                // since this table can't normally be created anymore, remove creation restrictions
                // ExtensionManagementUtility::allowTableOnStandardPages($foreignTable);
                $tca['pages']['ctrl']['EXT']['typo3api']['allow_tables'][] = $foreignTable;

                return [$tca];
            });
        }
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder)
    {
        if (!$tcaBuilder instanceof TableBuilderContext) {
            $type = is_object($tcaBuilder) ? get_class($tcaBuilder) : gettype($tcaBuilder);
            $msg = "Inline Relation is only supported on database tables";
            $msg .= " so the context must be a " . TableBuilderContext::class . ", got $type";
            throw new \RuntimeException($msg);
        }

        $column = [
            'type' => 'inline',
            'foreign_table' => $this->getOption('foreign_table'),
            'foreign_field' => $this->getOption('foreign_field'),
            'minitems' => $this->getOption('minitems'),
            'maxitems' => $this->getOption('maxitems'),
            'behaviour' => [
                'enableCascadingDelete' => $this->getOption('foreignTakeover'),
            ],
            'appearance' => [
                'collapseAll' => $this->getOption('collapseAll') ? 1 : 0,
                'enabledControls' => [
                    'info' => true,
                    'new' => true,
                    'delete' => true,
                ],
            ],
        ];

        $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $dispatcher->connect(ExtensionManagementUtility::class, 'tcaIsBeingBuilt', function ($tca) use ($tcaBuilder) {
            $foreignTable = $this->getOption('foreign_table');
            if (!isset($tca[$foreignTable])) {
                throw new \RuntimeException("$foreignTable not defined. Used in inline relation for $tcaBuilder");
            }

            $foreignTableDefinition = $tca[$foreignTable];
            $sortby = @$foreignTableDefinition['ctrl']['sortby'] ?: @$foreignTableDefinition['ctrl']['_sortby'];
            $canBeSorted = (bool)$sortby;
            $canLocalize = (bool)@$foreignTableDefinition['ctrl']['languageField'];
            $canHide = (bool)@$foreignTableDefinition['columns']['hidden'];

            $column =& $tca[$tcaBuilder->getTableName()]['columns'][$this->getName()];
            $column['config']['foreign_sortby'] = $sortby;
            $column['config']['behaviour']['localizeChildrenAtParentLocalization'] = $canLocalize;
            $column['config']['appearance']['useSortable'] = $canBeSorted;
            $column['config']['appearance']['showPossibleLocalizationRecords'] = $canLocalize;
            $column['config']['appearance']['showRemovedLocalizationRecords'] = $canLocalize;
            $column['config']['appearance']['showAllLocalizationLink'] = $canLocalize;
            $column['config']['appearance']['showSynchronizationLink'] = $canLocalize;
            $column['config']['appearance']['enabledControls']['dragdrop'] = $canBeSorted;
            $column['config']['appearance']['enabledControls']['sort'] = $canBeSorted;
            $column['config']['appearance']['enabledControls']['hide'] = $canHide;
            $column['config']['appearance']['enabledControls']['localize'] = $canLocalize;

            return [$tca];
        });

        return $column;
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
        $tableDefinitions[$this->getOption('foreign_table')] = [
            "`$foreignField` INT(11) DEFAULT '0' NOT NULL",
            "KEY `$foreignField`(`$foreignField`)",
        ];

        return $tableDefinitions;
    }
}
