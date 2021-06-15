<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;

class EnableColumnsConfiguration implements TcaConfigurationInterface, DefaultTabInterface
{
    public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder)
    {
        if (!isset($ctrl['enablecolumns'])) {
            $ctrl['enablecolumns'] = [];
        }
        $ctrl['enablecolumns']['disabled'] = 'hidden';
        $ctrl['enablecolumns']['starttime'] = 'starttime';
        $ctrl['enablecolumns']['endtime'] = 'endtime';
        $ctrl['enablecolumns']['fe_group'] = 'fe_group';

        $ctrl['editlock'] = 'editlock';
    }

    public function getColumns(TcaBuilderContext $tcaBuilder): array
    {
        return [
            'hidden' => $GLOBALS['TCA']['tt_content']['columns']['hidden'],
            'starttime' => $GLOBALS['TCA']['tt_content']['columns']['starttime'],
            'endtime' => $GLOBALS['TCA']['tt_content']['columns']['endtime'],
            'fe_group' => $GLOBALS['TCA']['tt_content']['columns']['fe_group'],
            'editlock' => $GLOBALS['TCA']['tt_content']['columns']['editlock'],
        ];
    }

    public function getPalettes(TcaBuilderContext $tcaBuilder): array
    {
        return [
            'hidden' => [
                'showitem' => implode(', ', [
                    'hidden;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.visibility'
                ])
            ],
            'access' => [
                'showitem' => implode(', ', [
                    'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel',
                    'endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
                    '--linebreak--',
                    'fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel',
                    '--linebreak--',
                    'editlock',
                ])
            ],
        ];
    }

    public function getShowItemString(TcaBuilderContext $tcaBuilder): string
    {
        return implode(', ', [
            '--palette--;;hidden',
            '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access'
        ]);
    }

    public function getDbTableDefinitions(TableBuilderContext $tableBuilder): array
    {
        return [];
    }

    public function getDefaultTab(): string
    {
        return 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access';
    }
}
