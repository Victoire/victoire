<?php

namespace Victoire\Bundle\I18nBundle\Route;

use Victoire\Bundle\CoreBundle\Route\RouteLoader as BaseRouteLoader;
use Victoire\Bundle\I18nBundle\Resolver\LocaleResolver; 
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class I18nRouteLoader extends BaseRouteLoader
{
	protected $localeResolver;

    public function __construct($widgets, LocaleResolver $localeResolver)
    {
        parent::__construct($widgets);
        $this->localeResolver = $localeResolver;
    }

    protected function addShowPageRoute(&$collection)
    {

        // prepare a new route
        $pattern = '/{_locale}/{url}';
        $defaults = array(
            '_controller' => 'VictoirePageBundle:Page:show',
            '_locale' => 'fr'
        );
        $requirements = array(
            'url' => '^.*$',
        );
        $route = new Route($pattern, $defaults, $requirements);

        // add the new route to the route collection:
        $routeName = 'victoire_core_page_show';

        $collection->add($routeName, $route);
    }
}
