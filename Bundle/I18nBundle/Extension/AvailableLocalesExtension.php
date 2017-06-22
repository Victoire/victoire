<?php

namespace Victoire\Bundle\I18nBundle\Extension;

class AvailableLocalesExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $availableLocales;

    public function __construct($availableLocales)
    {
        $this->availableLocales = $availableLocales;
    }

    public function getGlobals()
    {
        return [
            'victoire_i18n_available_locales' => $this->availableLocales,
        ];
    }
}
