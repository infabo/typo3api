<?php

namespace Typo3Api\Utility;


use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This utility is used by relational field configurations.
 */
class ForeignTableUtility
{
    public const ORDER_BY_REGEX = '/(\s*)(ORDER BY(.*))?$/i';

    public static function normalizeForeignTableWhere(string $foreignTableName, string $where): string
    {
        $foreignTable = $GLOBALS['TCA'][$foreignTableName] ?? [];

        // append sys_language_uid if available
        if (isset($foreignTable['ctrl']['languageField'])) {
            $languageField = $foreignTableName . '.' . $foreignTable['ctrl']['languageField'];
            $where = preg_replace(self::ORDER_BY_REGEX, "\\1 AND $languageField IN (0, -1) \\2", $where, 1);
        }

        // append sorting if available
        if (isset($foreignTable['ctrl']['sortby'])) {
            $sortByField = $foreignTableName . '.' . $foreignTable['ctrl']['sortby'];
            $where = preg_replace_callback(self::ORDER_BY_REGEX, function ($match) use ($sortByField) {
                if (isset($match[3])) {
                    return ' ORDER BY' . $match[3] . ', ' . $sortByField;
                }

                return ' ORDER BY ' . $sortByField;
            }, $where, 1);
        } else if (isset($foreignTable['ctrl']['default_sortby'])) {
            $sortByDefinitions = GeneralUtility::trimExplode(',', $foreignTable['ctrl']['default_sortby']);
            foreach ($sortByDefinitions as &$sortByDefinition) {
                $sortByDefinition = $foreignTableName . '.' . $sortByDefinition;
            }

            $sortByStr = implode(', ', $sortByDefinitions);
            $where = preg_replace_callback(self::ORDER_BY_REGEX, function ($match) use ($sortByStr) {
                if (isset($match[3])) {
                    return ' ORDER BY' . $match[3] . ', ' . $sortByStr;
                }

                return ' ORDER BY ' . $sortByStr;
            }, $where, 1);
        }

        return trim($where);
    }
}