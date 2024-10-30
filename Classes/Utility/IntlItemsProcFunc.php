<?php

declare(strict_types=1);

namespace Typo3Api\Utility;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;

class IntlItemsProcFunc
{
    public function addCountryNames(&$params): void
    {
        $countryNames = Countries::getNames();
        asort($countryNames);
        foreach ($countryNames as $countryCode => $countryName) {
            $params['items'][] = ['label' => $countryName, 'value' => $countryCode];
        }
    }

    public function addLanguages(&$params): void
    {
        $languageNames = Languages::getNames();
        asort($languageNames);
        foreach ($languageNames as $locale => $languageName) {
            $params['items'][] = ['label' => $languageName, 'value' => $locale];
        }
    }
}
