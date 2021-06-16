<?php


namespace Typo3Api;


use TYPO3\CMS\Core\Utility\GeneralUtility;

trait PreparationForTypo3
{
    private static $errorLevelBeforeClass = E_ALL;

    public static function setUpBeforeClass(): void
    {
        self::$errorLevelBeforeClass = error_reporting();
        error_reporting(self::$errorLevelBeforeClass & ~E_NOTICE);
        // what? you think you can execute typo3 code with notices enabled? are you crazy?
    }

    public static function tearDownAfterClass(): void
    {
        error_reporting(self::$errorLevelBeforeClass);
    }

    protected function setUp(): void
    {
        // load tt_content tca because it is used as a reference in many configurations
        $GLOBALS['TYPO3_CONF_VARS'] = require __DIR__ . '/../web/typo3/sysext/core/Configuration/DefaultConfiguration.php';
        $tca = require __DIR__ . '/../web/typo3/sysext/frontend/Configuration/TCA/tt_content.php';
        if (is_array($tca)) {
            $GLOBALS['TCA']['tt_content'] = $tca;
        }
    }

    public function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        unset($GLOBALS['TCA']);
    }
}
