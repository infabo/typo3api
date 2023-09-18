<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['typo3api'] =
    \Typo3Api\Hook\CacheTagHook::class . '->clearCachePostProcess';
