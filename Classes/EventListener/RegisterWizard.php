<?php

declare(strict_types=1);

namespace Typo3Api\EventListener;

use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class RegisterWizard
{
    public function __invoke(AfterTcaCompilationEvent $event): void
    {
        if (!isset($event->getTca()['tt_content']['ctrl']['EXT']['typo3api']['content_elements'])) {
            return;
        }

        foreach ($event->getTca()['tt_content']['ctrl']['EXT']['typo3api']['content_elements'] as $section => $contentElements) {
            foreach ($contentElements as $contentElement) {
                ExtensionManagementUtility::addPageTSConfig(
                    <<<EOD
mod.wizards.newContentElement.wizardItems.{$section} {
  elements {
    {$contentElement['CType']} {
      iconIdentifier = {$contentElement['iconIdentifier']}
      title = {$contentElement['title']}
      description = {$contentElement['description']}
      tt_content_defValues {
        CType = {$contentElement['CType']}
      }
    }
  }
  show := addToList({$contentElement['CType']})
}
EOD
                );
            }
        }
    }
}
