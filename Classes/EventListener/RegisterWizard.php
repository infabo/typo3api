<?php

declare(strict_types=1);

namespace Typo3Api\EventListener;

use TYPO3\CMS\Core\Configuration\Event\ModifyLoadedPageTsConfigEvent;

class RegisterWizard
{
    public function __invoke(ModifyLoadedPageTsConfigEvent $event): void
    {
        if (!isset($GLOBALS['TCA']['tt_content']['ctrl']['EXT']['typo3api']['content_elements'])) {
            return;
        }

        foreach ($GLOBALS['TCA']['tt_content']['ctrl']['EXT']['typo3api']['content_elements'] as $section => $contentElements) {
            foreach ($contentElements as $contentElement) {
                $event->addTsConfig(
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
