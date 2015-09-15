<?php

namespace Victoire\Bundle\I18nBundle\Resolver;

use Symfony\Component\HttpFoundation\Request;

/**
* A class to guess the locale form URL
* ref: victoire_i18n.locale_resolver
*/
class LocaleResolver
{
    const PATTERN_PARAMETER = 'parameter'; // pass the locale the normal way, ie. http://acme.dn/fr
    const PATTERN_DOMAIN    = 'domain';

    public $localePattern;
    protected $localeDomainConfig;
    protected $availableLocales;
    public $defaultLocale;
    public $defaultDomain;

    /**
     * Constructor
     *
     * @param string $localePattern      What is the strategy to resolve locale
     * @param array  $localeDomainConfig The locale domain config
     * @param string $defaultLocale      The default local app
     * @param string $availableLocales   The list of available locales
     */
    public function __construct($localePattern, array $localeDomainConfig, $defaultLocale, $availableLocales)
    {
        $this->localePattern = $localePattern;
        $this->localeDomainConfig = $localeDomainConfig;
        $this->defaultLocale = $defaultLocale;
        $this->availableLocales = $availableLocales;

        foreach ($this->localeDomainConfig as $_domain => $_locale) {
            if ($_locale == $this->defaultLocale) {
                $this->defaultDomain = $_domain;
                break;
            }
        }
    }

    /**
     * set the local depending on patterns
     * it also set the victoire_locale wich is the locale of the application admin
     */
    public function resolve(Request $request)
    {
        //locale
        switch ($this->localePattern) {
            case self::PATTERN_DOMAIN :
                $locale = $this->resolveFromDomain($request);
                $request->setLocale($locale);
                break;
        }

        return $request->getLocale();
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     * @return string
     *
     * resolves the locale from httpHost or host
     */
    public function resolveFromDomain(Request $request)
    {
        $host = $request->getHost();
        $httpHost = $request->getHttpHost();

        if (array_key_exists($host, $this->localeDomainConfig)) {
            return $this->localeDomainConfig[$host];
        } else if (array_key_exists($httpHost, $this->localeDomainConfig)) {
            return $this->localeDomainConfig[$httpHost];
        }

        error_log(sprintf(
            'Host "%s" is not defined in your locale_pattern_table in app/config/victoire_core.yml (%s available), using default locale (%s) instead',
            $httpHost,
            implode(',', $this->localeDomainConfig),
            $this->defaultLocale
        ));

    }

    /**
     *
     * @return string
     *
     * This method resolves the domain from locale
     */
    public function resolveDomainForLocale($locale)
    {
        foreach ($this->localeDomainConfig as $domain => $domainLocale) {
            if ($locale === $domainLocale) {
                return $domain;
            }
        }

        return $this->defaultLocale;
    }

    /**
     * Return available locales
     * @return array
     */
    public function getAvailableLocales()
    {
        return $this->availableLocales;
    }

    /**
     * return domain config
     * @return array
     */
    public function getDomainConfig()
    {
        return $this->localeDomainConfig;
    }
}
