<?php

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
            $params['items'][] = [$countryName, $countryCode];
        }
    }

    public function addLanguages(&$params): void
    {
        $languageNames = Languages::getNames();
        asort($languageNames);
        foreach ($languageNames as $locale => $languageName) {
            $params['items'][] = [$languageName, $locale];
        }
    }
}