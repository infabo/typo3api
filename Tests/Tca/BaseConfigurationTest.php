<?php

namespace Typo3Api\Tca;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Typo3Api\Builder\TableBuilder;
use Typo3Api\Hook\SqlSchemaHookUtil;
use Typo3Api\PreparationForTypo3;

class BaseConfigurationTest extends UnitTestCase
{
    use PreparationForTypo3;
    use SqlSchemaHookUtil;

    const BASE_SQL = [];

    const BASE_TCA = [
        'ctrl' => [
            'delete' => 'deleted',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'origUid' => 'origUid',
            'title' => 'Test table',
            'label' => 'uid',
            'EXT' => [
                'typo3api' => [
                    'sql' => [
                        'test_table' => self::BASE_SQL
                    ]
                ]
            ]
        ],
        'columns' => [],
        'types' => [
            '1' => []
        ],
        'palettes' => [],
    ];

    public function testConfiguration(): void
    {
        TableBuilder::create('test_table');
        // the base configuration is applied automatically

        $this->assertEquals(self::BASE_TCA, $GLOBALS['TCA']['test_table']);
        $this->assertSqlInserted(['test_table' => self::BASE_SQL]);
    }
}
