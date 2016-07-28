<?php

namespace Victoire\Bundle\CoreBundle\Twig\Extension;

use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Extension\RoutingExtension as BaseRoutingExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Victoire\Bundle\I18nBundle\Resolver\LocaleResolver;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Victoire\Bundle\ViewReferenceBundle\Exception\ViewReferenceNotFoundException;

/**
 * class RoutingExtension.
 */
class RoutingExtention extends BaseRoutingExtension
{
    private $pageHelper;
    private $generator;
    private $localeResolver;
    private $logger;
    private $errorPageRepository;
    private $locale;

    /**
     * @param PageHelper            $pageHelper
     * @param UrlGeneratorInterface $generator
     * @param LocaleResolver        $localeResolver
     * @param RequestStack          $requestStack
     * @param LoggerInterface       $logger
     * @param EntityRepository      $errorPageRepository
     * @param                       $locale
     */
    public function __construct(
        PageHelper $pageHelper,
        UrlGeneratorInterface $generator,
        LocaleResolver $localeResolver,
        RequestStack $requestStack,
        LoggerInterface $logger,
        EntityRepository $errorPageRepository,
        $locale
    ) {
        $this->pageHelper = $pageHelper;
        $this->generator = $generator;
        $this->localeResolver = $localeResolver;
        $this->request = $requestStack->getCurrentRequest();
        $this->logger = $logger;
        $this->errorPageRepository = $errorPageRepository;
        parent::__construct($generator);
        $this->locale = $locale;
    }

    public function getPath($name, $parameters = [], $relative = false)
    {
        $requestLocale = $this->request ? $this->request->getLocale() : $this->defaultLocale;
        if ($name == 'victoire_core_page_show_by_id') {
            $params = [
                'viewId' => $parameters['viewId'],
                'locale' => $requestLocale,
            ];
            unset($parameters['viewId']);
            if (!empty($parameters['entityId'])) {
                $params['entityId'] = $parameters['entityId'];
                unset($parameters['entityId']);
            }
            try {
                $page = $this->pageHelper->findPageByParameters($params);
                $parameters['url'] = $page->getReference($requestLocale)->getUrl();
            } catch (ViewReferenceNotFoundException $e) {
                $this->logger->error($e->getMessage(), [
                    'params' => $params,
                ]);
                $errorPage = $this->errorPageRepository->findOneByCode(404);
                $parameters['url'] = $this->generator->generate(
                    'victoire_core_page_show', array_merge([
                        '_locale' => $requestLocale,
                        'url'     => $errorPage ? $errorPage->getSlug() : '',
                    ], $parameters
                ));
            }

            $name = 'victoire_core_page_show';
        }

        $prefix = '';
        //if locale is passed (and different) and i18n strategy is "domain"
        if (!empty($parameters['_locale'])
            && $parameters['_locale'] != $requestLocale
            && $this->localeResolver->localePattern === LocaleResolver::PATTERN_DOMAIN
        ) {
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
                $urlPrefix = sprintf('%s://%s', $this->request ? $this->request->getScheme() : 'http', $_domain);
                if ($this->request && $this->request->getPort()) {
                    $urlPrefix .= ':'.$this->request->getPort();
                }

                return $urlPrefix;
            }
        }
    }
}
