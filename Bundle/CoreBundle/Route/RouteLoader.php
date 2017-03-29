<?php

namespace Victoire\Bundle\CoreBundle\Route;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader
{
    protected $widgets;

    public function __construct($widgets)
    {
        $this->widgets = $widgets;
    }

    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();

        $this->addVictoireRouting($collection);
        $this->addWidgetsRouting($collection);
        $this->addShowPageByIdRoute($collection);
        $this->addShowBusinessPageByIdAction($collection);
        $this->addShowPageRoute($collection);
        $this->addShowHomePageRoute($collection);

        return $collection;
    }

    protected function addVictoireRouting(RouteCollection &$collection)
    {
        $resources = [
            '@VictoireAnalyticsBundle/Controller/',
            '@VictoireTemplateBundle/Controller/',
            '@VictoireTwigBundle/Controller/',
            '@VictoireBlogBundle/Controller/',
            '@VictoireBusinessPageBundle/Controller/',
            '@VictoireSeoBundle/Controller/',
            '@VictoireMediaBundle/Controller/',
            '@VictoirePageBundle/Controller/',
            '@VictoireCoreBundle/Controller/',
            '@VictoireWidgetBundle/Controller/',
            '@VictoireSitemapBundle/Controller/',
            '@VictoireAPIBusinessEntityBundle/Controller/',
        ];
        foreach ($resources as $resource) {
            $importedRoutes = $this->import($resource, 'annotation');
            $collection->addCollection($importedRoutes);
        }
    }

    protected function addWidgetsRouting(RouteCollection &$collection)
    {
        foreach ($this->widgets as $widgetParams) {
            $controllerResource = '@VictoireWidget'.$widgetParams['name'].'Bundle/Controller/';
            if ($this->getResolver()->resolve($controllerResource)) {
                $importedRoutes = $this->import($controllerResource, 'annotation');
                $collection->addCollection($importedRoutes);
            }
        }
    }

    protected function addShowBusinessPageByIdAction(RouteCollection &$collection)
    {
        $pattern = '/victoire-dcms-public/show-business-page-by-id/{entityId}/{type}';
        $defaults = [
            '_controller' => 'VictoirePageBundle:Page:showBusinessPageById',
        ];
        $requirements = [
            'viewId' => '\d+',
        ];
        $route = new Route($pattern, $defaults, $requirements);
        $routeName = 'victoire_core_business_page_show_by_id';
        $collection->add($routeName, $route);
    }

    protected function addShowPageByIdRoute(RouteCollection &$collection)
    {
        $pattern = '/victoire-dcms-public/show-page-by-id/{viewId}/{entityId}';
        $defaults = [
            '_controller' => 'VictoirePageBundle:Page:showById',
            'entityId'    => null,
        ];
        $requirements = [
            'viewId' => '\d+',
        ];
        $options = [
            'expose' => true,
        ];
        $route = new Route($pattern, $defaults, $requirements, $options);
        $routeName = 'victoire_core_page_show_by_id';
        $collection->add($routeName, $route);
    }

    protected function addShowPageRoute(RouteCollection &$collection)
    {
        // prepare a new route
        $pattern = '/{url}';
        $defaults = [
            '_controller' => 'VictoirePageBundle:Page:show',
        ];
        $requirements = [
            'url' => '^.*$',
        ];
        $route = new Route($pattern, $defaults, $requirements);

        // add the new route to the route collection:
        $collection->add('victoire_core_page_show', $route);
    }

    protected function addShowHomePageRoute(RouteCollection &$collection)
    {
        // prepare a new route
        $pattern = '/';
        $defaults = [
            '_controller' => 'VictoirePageBundle:Page:show',
        ];
        $options = [
            'expose' => true,
        ];
        $route = new Route($pattern, $defaults, [], $options);

        // add the new route to the route collection:
        $collection->add('victoire_core_homepage_show', $route);
    }

    public function supports($resource, $type = null)
    {
        return $type === 'victoire';
    }
}
