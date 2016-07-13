<?php

namespace Victoire\Bundle\CoreBundle\Twig\Extension;

use Symfony\Bridge\Twig\Extension\RoutingExtension as BaseRoutingExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Victoire\Bundle\I18nBundle\Resolver\LocaleResolver;
use Victoire\Bundle\PageBundle\Helper\PageHelper;

/**
 * class RoutingExtension.
 */
class RoutingExtention extends BaseRoutingExtension
{
    private $pageHelper;
    private $generator;
    private $localeResolver;

    /**
     * @param PageHelper            $pageHelper
     * @param UrlGeneratorInterface $generator
     * @param LocaleResolver        $localeResolver
     */
    public function __construct(PageHelper $pageHelper, UrlGeneratorInterface $generator, LocaleResolver $localeResolver, RequestStack $requestStack)
    {
        $this->pageHelper = $pageHelper;
        $this->generator = $generator;
        $this->localeResolver = $localeResolver;
        $this->request = $requestStack->getCurrentRequest();
        parent::__construct($generator);
    }

    public function getPath($name, $parameters = [], $relative = false)
    {
        if ($name == 'victoire_core_page_show_by_id') {
            $params = [
                'viewId' => $parameters['viewId'],
                'locale' => $this->request->getLocale(),
            ];
            unset($parameters['viewId']);
            if (!empty($parameters['entityId'])) {
                $params['entityId'] = $parameters['entityId'];
                unset($parameters['entityId']);
            }

            try {
                $page = $this->pageHelper->findPageByParameters($params);
                $parameters['url'] = $page->getReference($this->request->getLocale())->getUrl();
            } catch (\Exception $e) {
                error_log($e->getMessage());
                $parameters['url'] = '410';
                $parameters = array_merge($parameters, $params);
            }

            $name = 'victoire_core_page_show';
        }

        $prefix = '';
        //if locale is passed (and different) and i18n strategy is "domain"
        if (!empty($parameters['_locale']) && $parameters['_locale'] != $this->request->getLocale() && $this->localeResolver->localePattern === LocaleResolver::PATTERN_DOMAIN) {
            $prefix = $this->getPrefix($parameters['_locale']);
            //if we set a prefix, we don't want an absolute path
            if ($prefix) {
                $relative = true;
            }
        }

        return $prefix.$this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::ABSOLUTE_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * If victoire_i18n.locale_pattern == domain, then we force the url rewrite with a valid host.
     *
     * @param $locale
     *
     * @return null
     */
    protected function getPrefix($locale)
    {
        foreach ($this->localeResolver->getDomainConfig() as $_domain => $_locale) {
            if ($_locale === $locale) {
                $urlPrefix = sprintf('%s://%s', $this->request->getScheme(), $_domain);
                if ($this->request->getPort()) {
                    $urlPrefix .= ':'.$this->request->getPort();
                }

                return $urlPrefix;
            }
        }
    }
}
