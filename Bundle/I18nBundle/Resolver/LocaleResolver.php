<?php

namespace Victoire\Bundle\I18nBundle\Resolver;

use Symfony\Component\HttpFoundation\Request;

/**
 * A class to guess the locale form URL
 * ref: victoire_i18n.locale_resolver.
 */
class LocaleResolver
{
    const PATTERN_PARAMETER = 'parameter'; // pass the locale the normal way, ie. http://acme.dn/fr
    const PATTERN_DOMAIN = 'domain';

    public $localePattern;
    protected $localeDomainConfig;
    protected $availableLocales;
    public $defaultLocale;

    /**
     * Constructor.
     *
     * @param string $localePattern      What is the strategy to resolve locale
     * @param string $localeDomainConfig The locale domain config
     * @param string $defaultLocale      The default local app
     * @param string $availableLocales   The list of available locales
     */
    public function __construct($localePattern, $localeDomainConfig, $defaultLocale, $availableLocales)
    {
        $this->localePattern = $localePattern;
        $this->localeDomainConfig = $localeDomainConfig;
        $this->defaultLocale = $defaultLocale;
        $this->availableLocales = $availableLocales;
    }

    /**
     * set the local depending on patterns
     * it also set the victoire_locale wich is the locale of the application admin.
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
     *
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
        } elseif (array_key_exists($httpHost, $this->localeDomainConfig)) {
            return $this->localeDomainConfig[$httpHost];
        }

        throw new \Exception(sprintf(
            'Host "%s" is not defined in your locale_pattern_table in app/config/victoire_core.yml (%s available)',
            $httpHost,
            implode(',', $this->localeDomainConfig)
        ));
    }

    /**
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
}
