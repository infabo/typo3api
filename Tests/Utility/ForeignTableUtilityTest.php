<?php

namespace Typo3Api\Utility;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ForeignTableUtilityTest extends UnitTestCase
{
    public function tearDown(): void
    {
        unset($GLOBALS['TCA']);
    }

    public static function cases(): array
    {
        return [
            'nothing' => [
                ['ctrl' => []],
                '',
                '',
            ],
            'orderBy' => [
                ['ctrl' => ['sortby' => 'sorting']],
                '',
                'ORDER BY table.sorting',
            ],
            'doubleOrderBy' => [
                ['ctrl' => ['sortby' => 'sorting']],
                'ORDER BY uid',
                'ORDER BY uid, table.sorting',
            ],
            'where' => [
                ['ctrl' => []],
                'AND pid = 5',
                'AND pid = 5',
            ],
            'whereOrderBy' => [
                ['ctrl' => ['sortby' => 'sorting']],
                'AND pid = 5',
                'AND pid = 5 ORDER BY table.sorting',
            ],
            'whereDoubleOrderBy' => [
                ['ctrl' => ['sortby' => 'sorting']],
                'AND pid = 5 ORDER BY uid',
                'AND pid = 5 ORDER BY uid, table.sorting',
            ],
            'defaultSortBy' => [
                ['ctrl' => ['default_sortby' => 'sorting ASC']],
                '',
                'ORDER BY table.sorting ASC',
            ],
            'defaultSortBy+SortBy+Where' => [
                ['ctrl' => ['default_sortby' => 'sorting ASC']],
                'pid = 5 ORDER BY uid',
                'pid = 5 ORDER BY uid, table.sorting ASC',
            ],
            'defaultSortBy+SortBy' => [
                ['ctrl' => ['sortby' => 'sorting', 'default_sortby' => 'uid ASC']],
                '',
                'ORDER BY table.sorting',
            ],
            'languageField' => [
                ['ctrl' => ['languageField' => 'sys_language_uid']],
                '',
                'AND table.sys_language_uid IN (0, -1)'
            ],
            'languageField+everything' => [
                ['ctrl' => ['languageField' => 'sys_language_uid', 'sortby' => 'sorting']],
                'AND pid = 5',
                'AND pid = 5 AND table.sys_language_uid IN (0, -1) ORDER BY table.sorting'
            ]
        ];
    }

    /**
     * @param array $tca
     * @param string $where
     * @param string $expect
     *
     * @dataProvider cases
     */
    public function testNormalization(array $tca, string $where, string $expect): void
    {
        $GLOBALS['TCA']['table'] = $tca;
        $this->assertEquals($expect, ForeignTableUtility::normalizeForeignTableWhere('table', $where));
    }
}
