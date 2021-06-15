<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;

class LanguageConfiguration implements TcaConfigurationInterface, DefaultTabInterface
{
    public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder)
    {
        $ctrl['languageField'] = 'sys_language_uid';
        $ctrl['translationSource'] = 'l10n_source';
        $ctrl['transOrigPointerField'] = 'l18n_parent';
        $ctrl['transOrigDiffSourceField'] = 'l18n_diffsource';
    }

    public function getColumns(TcaBuilderContext $tcaBuilder): array
    {
        if (!$tcaBuilder instanceof TableBuilderContext) {
            throw new \LogicException("LanguageConfiguration only possible on database tables");
        }

        $tableName = $tcaBuilder->getTableName();
        return [
            'sys_language_uid' => [
                'exclude' => false,
                'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.language',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'special' => 'languages',
                    'items' => [
                        [
                            'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                            -1,
                            'flags-multiple'
                        ],
                    ],
                    'default' => 0,
                ]
            ],
            'l10n_source' => [
                'config' => [
                    'type' => 'passthrough'
                ]
            ],
            'l18n_diffsource' => [
                'config' => [
                    'type' => 'passthrough',
                    'default' => ''
                ]
            ],
            'l18n_parent' => [
                'exclude' => false,
                'displayCond' => 'FIELD:sys_language_uid:>:0',
                'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['', 0]
                    ],
                    'foreign_table' => $tableName,
                    'foreign_table_where' => "AND $tableName.pid=###CURRENT_PID### AND $tableName.sys_language_uid IN (-1,0)",
                    'default' => 0
                ]
            ],
            // TODO, wrong localization fieldnames, what is l18n supposed to mean?
            // l10n = localization
            // i18n = internationalization
            // l18n = ?
            // it should be l10n like with the source field but that would be a breaking change for a future release
        ];
    }

    public function getPalettes(TcaBuilderContext $tcaBuilder): array
    {
        return [
            'language' => [
                'showitem' => implode(', ', [
                    'sys_language_uid;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:sys_language_uid_formlabel',
                    'l18n_parent'
                ])
            ]
        ];
    }

    public function getShowItemString(TcaBuilderContext $tcaBuilder): string
    {
        return "--palette--;;language";
    }

    public function getDbTableDefinitions(TableBuilderContext $tableBuilder): array
    {
        return [
            $tableBuilder->getTableName() => [
                "sys_language_uid int(11) DEFAULT '0' NOT NULL",
                "l10n_source int(11) DEFAULT '0' NOT NULL",
                "l18n_diffsource mediumtext",
                "l18n_parent int(11) DEFAULT '0' NOT NULL",
                "KEY language (l18n_parent, sys_language_uid)",
            ]
        ];
    }

    public function getDefaultTab(): string
    {
        return 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language';
    }
}
