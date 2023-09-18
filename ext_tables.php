<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

if (isset($GLOBALS['TCA']['pages']['ctrl']['EXT']['typo3api']['allow_tables'])) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
        implode(',', $GLOBALS['TCA']['pages']['ctrl']['EXT']['typo3api']['allow_tables'])
    );
}
