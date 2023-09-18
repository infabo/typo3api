<?php

declare(strict_types=1);

namespace Typo3Api\Hook;

use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CacheTagHook
{
    /**
     * @throws NoSuchCacheGroupException
     */
    public function clearCachePostProcess(array $params): void
    {
        if (!isset($GLOBALS['TCA'][$params['table']]['ctrl']['EXT']['typo3api']['cache_tags'])) {
            return;
        }

        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);

        foreach ($GLOBALS['TCA'][$params['table']]['ctrl']['EXT']['typo3api']['cache_tags'] as $group => $tags) {
            foreach ($tags as &$tag) {
                $tag = str_replace(
                    ['###UID###', '###PID###'],
                    [$params['uid'], $params['uid_page']],
                    (string) $tag
                );
            }

            $cacheManager->flushCachesInGroupByTags($group, $tags);
        }
    }
}
