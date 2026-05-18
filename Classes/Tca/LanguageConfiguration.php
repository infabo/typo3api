<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;

class LanguageConfiguration implements TcaConfigurationInterface, DefaultTabInterface
{
    public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder): void
    {
        $ctrl['languageField'] = 'sys_language_uid';
        $ctrl['translationSource'] = 'l10n_source';
        $ctrl['transOrigPointerField'] = 'l18n_parent';
        $ctrl['transOrigDiffSourceField'] = 'l18n_diffsource';
    }

    public function getColumns(TcaBuilderContext $tcaBuilder): array
    {
        if (!$tcaBuilder instanceof TableBuilderContext) {
            throw new \LogicException("LanguageConfiguration only possible on database tables", 5253613794);
        }

        return [];
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
                "KEY language (l18n_parent, sys_language_uid)",
            ]
        ];
    }

    public function getDefaultTab(): string
    {
        return 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language';
    }
}
