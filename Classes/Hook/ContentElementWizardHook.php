<?php

namespace Typo3Api\Hook;

use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;

class ContentElementWizardHook implements NewContentElementWizardHookInterface
{
    /**
     * Modifies WizardItems array
     *
     * @param array $wizardItems Array of Wizard Items
     * @param \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController $parentObject Parent object New Content element wizard
     */
    public function manipulateWizardItems(&$wizardItems, &$parentObject): void
    {
        if (!isset($GLOBALS['TCA']['tt_content']['ctrl']['EXT']['typo3api']['content_elements'])) {
            return;
        }

        $keys = array_keys($wizardItems);
        $values = array_values($wizardItems);

        foreach ($GLOBALS['TCA']['tt_content']['ctrl']['EXT']['typo3api']['content_elements'] as $section => $contentElements) {
            $sectionIndex = array_search($section, $keys, true);
            array_walk($contentElements, static function (&$contentElement) {
                $contentElement['title'] = $GLOBALS['LANG']->sL($contentElement['title']);
                $contentElement['description'] = $GLOBALS['LANG']->sL($contentElement['description']);
            });
            array_splice($values, $sectionIndex + 1, 0, $contentElements);
            array_splice($keys, $sectionIndex + 1, 0, array_map(static function ($contentElement) use ($section) {
                return $section . '_' . $contentElement['CType'];
            }, $contentElements));
        }

        $wizardItems = array_combine($keys, $values);
    }
}
