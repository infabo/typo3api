<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Typo3Api\Builder\TableBuilder;
use Typo3Api\Hook\SqlSchemaHookUtil;
use Typo3Api\PreparationForTypo3;

class VersioningConfigurationTest extends UnitTestCase
{
    use PreparationForTypo3;
    use SqlSchemaHookUtil;

    public function testConfigure(): void
    {
        TableBuilder::create('test_table')
            ->configure(new VersioningConfiguration());

        // TYPO3's DefaultTcaSchema handles the t3ver_* columns automatically,
        // so no SQL definitions are emitted by this configuration.
        $sql = BaseConfigurationTest::BASE_TCA['ctrl']['EXT']['typo3api']['sql'];

        $this->assertEquals(
            array_replace_recursive(
                BaseConfigurationTest::BASE_TCA,
                [
                    'ctrl' => [
                        'versioningWS' => true,
                    ],
                ]
            ),
            $GLOBALS['TCA']['test_table']
        );

        $this->assertSqlInserted($sql);
    }

    public function testVersioningWsCtrlFlagIsSet(): void
    {
        TableBuilder::create('test_table')
            ->configure(new VersioningConfiguration());

        $this->assertTrue($GLOBALS['TCA']['test_table']['ctrl']['versioningWS']);
    }

    public function testNoShowItemIsAdded(): void
    {
        TableBuilder::create('test_table')
            ->configure(new VersioningConfiguration());

        // Versioning fields must not appear in the form layout
        $showitem = $GLOBALS['TCA']['test_table']['types']['1']['showitem'] ?? '';
        $this->assertStringNotContainsString('t3ver_', $showitem);
    }
}



