<?php

namespace Victoire\Bundle\I18nBundle\Route;

use Gedmo\Sluggable\Util\Urlizer;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Victoire\Bundle\CoreBundle\Route\RouteLoader as BaseRouteLoader;
use Victoire\Bundle\I18nBundle\Resolver\LocaleResolver;

/**
 * The I18nRouteLoader overwrite Victoire default RouteLoader to.
 */
class I18nRouteLoader extends BaseRouteLoader
{
    /**
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * RouteLoader constructor.
     *
     * @param array          $widgets
     * @param Kernel         $kernel
     * @param Logger         $logger
     * @param LocaleResolver $localeResolver
     */
    public function __construct($widgets, Kernel $kernel, Logger $logger, LocaleResolver $localeResolver)
    {
        parent::__construct($widgets, $kernel, $logger);
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();
        foreach ($this->localeResolver->getDomainConfig() as $_domain => $_locale) {
            $_collection = parent::load($resource, $type);
            foreach ($_collection->all() as $_name => $_route) {
                $_route->addDefaults(
                    [
                        '_locale' => $_locale,
                    ]
                );
                $_route->setHost($_domain);
                $collection->add(
                    sprintf(
                        '%s__%s__%s',
                        $_locale,
                        Urlizer::urlize($_domain, '_'),
                        $_name
                    ),
                    $_route
                );
            }
        }

        /*
         * Add default(fallback) route for default locale/domain
         * needs to be after the loop
         * */
        $defaultCollection = parent::load($resource, $type);
        $defaultCollection->addDefaults(['_locale' => $this->localeResolver->defaultLocale]);
        $collection->addCollection($defaultCollection);

        if ($this->localeResolver->localePattern == LocaleResolver::PATTERN_PARAMETER) {
            $collection = parent::load($resource, $type);
            //Prefix every victoire route with the locale
            $collection->addPrefix('/{_locale}');
            $collection->addCollection($collection);
            //Add a redirection to the default locale homepage when empty url '/'
            $this->addHomepageRedirection($collection);
        }

        return $collection;
    }

    /**
     * Add a homepage redirection route to the collection.
     *
     * @param RouteCollection $collection The collection where to add the new route
     */
    protected function addHomepageRedirection(&$collection)
    {
        $route = new Route(
            '/',
            [
                '_controller' => 'FrameworkBundle:Redirect:urlRedirect',
                'path'        => '/'.$this->localeResolver->defaultLocale,
                'permanent'   => true,
            ]
        );

        $collection->add('victoire_redirect_homepage', $route);
    }

    /**
     * Finds a loader able to load an imported resource.
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return $type === 'victoire_i18n';
    }
}
