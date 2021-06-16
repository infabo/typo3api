<?php

namespace Typo3Api\Tca;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Typo3Api\Builder\TableBuilder;
use Typo3Api\Hook\SqlSchemaHookUtil;
use Typo3Api\PreparationForTypo3;

class EnableColumnsConfigurationTest extends UnitTestCase
{
    use PreparationForTypo3;
    use SqlSchemaHookUtil;

    public function testConfigure(): void
    {
        TableBuilder::create('test_table')
            ->configure(new EnableColumnsConfiguration())
        ;

        $sql = [
            "test_table" => BaseConfigurationTest::BASE_SQL
        ];

        $this->assertEquals(array_replace_recursive(
            BaseConfigurationTest::BASE_TCA,
            [
                'ctrl' => [
                    'editlock' => 'editlock',
                    'enablecolumns' => [
                        'disabled' => 'hidden',
                        'starttime' => 'starttime',
                        'endtime' => 'endtime',
                        'fe_group' => 'fe_group',
                    ],
                    'EXT' => [
                        'typo3api' => [
                            'sql' => $sql
                        ]
                    ]
                ],
                'columns' => [
                    'hidden' => $GLOBALS['TCA']['tt_content']['columns']['hidden'],
                    'starttime' => $GLOBALS['TCA']['tt_content']['columns']['starttime'],
                    'endtime' => $GLOBALS['TCA']['tt_content']['columns']['endtime'],
                    'fe_group' => $GLOBALS['TCA']['tt_content']['columns']['fe_group'],
                    'editlock' => $GLOBALS['TCA']['tt_content']['columns']['editlock'],
                ],
                'types' => [
                    '1' => [
                        'showitem' => implode(', ', [
                            '--div--; LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access',
                            '--palette--;;hidden',
                            '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access',
                        ]),
                    ],
                ],
                'palettes' => [
                    'hidden' => [
                        'showitem' => implode(', ', [
                            'hidden;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.visibility'
                        ]),
                    ],
                    'access' => [
                        'showitem' => implode(', ', [
                            'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel',
                            'endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
                            '--linebreak--',
                            'fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel',
                            '--linebreak--',
                            'editlock',
                        ]),
                    ],
                ],
            ]
        ), $GLOBALS['TCA']['test_table']);

        $this->assertSqlInserted($sql);
    }
}
