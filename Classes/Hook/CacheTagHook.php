<?php

declare(strict_types=1);

namespace Typo3Api\Hook;

use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException;
use TYPO3\CMS\Core\Cache\CacheManager;

class CacheTagHook
{
    public function __construct(private readonly \TYPO3\CMS\Core\Cache\CacheManager $cacheManager)
    {
    }
    /**
     * @throws NoSuchCacheGroupException
     */
    public function clearCachePostProcess(array $params): void
    {
        if (!isset($GLOBALS['TCA'][$params['table']]['ctrl']['EXT']['typo3api']['cache_tags'])) {
            return;
        }

        $cacheManager = $this->cacheManager;

        foreach ($GLOBALS['TCA'][$params['table']]['ctrl']['EXT']['typo3api']['cache_tags'] as $group => $tags) {
            foreach ($tags as &$tag) {
                $tag = str_replace(
                    ['###UID###', '###PID###'],
                    [$params['uid'], $params['uid_page']],
                    $tag
                );
            }

            $cacheManager->flushCachesInGroupByTags($group, $tags);
        }
    }
}
