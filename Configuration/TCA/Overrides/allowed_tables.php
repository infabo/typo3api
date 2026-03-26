<?php

declare(strict_types=1);

(static function () {
    if (isset($GLOBALS['TCA']['pages']['ctrl']['EXT']['typo3api']['allow_tables'])) {
        foreach ($GLOBALS['TCA']['pages']['ctrl']['EXT']['typo3api']['allow_tables'] as $table) {
            $GLOBALS['TCA'][$table]['ctrl']['security']['ignorePageTypeRestriction'] = true;
        }
    }
})();
