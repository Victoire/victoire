<?php
namespace Victoire\Bundle\CoreBundle\Route;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

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

        return $collection;
    }

    protected function addVictoireRouting(&$collection)
    {
        $resources = array(
            '@VictoireAnalyticsBundle/Controller/',
            '@VictoireTemplateBundle/Controller/',
            '@VictoireBlogBundle/Controller/',
            '@VictoireBusinessPageBundle/Controller/',
            '@VictoireSeoBundle/Controller/',
            '@VictoireMediaBundle/Controller/',
            '@VictoirePageBundle/Controller/',
            '@VictoireCoreBundle/Controller/',
            '@VictoireWidgetBundle/Controller/',
            '@VictoireSitemapBundle/Controller/',
        );
        foreach ($resources as $resource) {
            $importedRoutes = $this->import($resource, 'annotation');
            $collection->addCollection($importedRoutes);
        }
    }
    protected function addWidgetsRouting(&$collection)
    {
        foreach ($this->widgets as $widgetParams) {
            $controllerResource = '@VictoireWidget'.$widgetParams['name'].'Bundle/Controller/';
            if ($this->getResolver()->resolve($controllerResource)) {
                $importedRoutes = $this->import($controllerResource, 'annotation');
                $collection->addCollection($importedRoutes);
            }
        }
    }

    protected function addShowBusinessPageByIdAction(&$collection)
    {
        $pattern = '/victoire-dcms-public/show-business-page-by-id/{entityId}/{type}';
        $defaults = array(
            '_controller' => 'VictoirePageBundle:Page:showBusinessPageById',
        );
        $requirements = array(
            'viewId' => '\d+',
        );
        $route = new Route($pattern, $defaults, $requirements);
        $routeName = 'victoire_core_business_page_show_by_id';
        $collection->add($routeName, $route);
    }

    protected function addShowPageByIdRoute(&$collection)
    {
        $pattern = '/victoire-dcms-public/show-page-by-id/{viewId}/{entityId}';
        $defaults = array(
            '_controller' => 'VictoirePageBundle:Page:showById',
            'entityId' => null,
        );
        $requirements = array(
            'viewId' => '\d+',
        );
        $route = new Route($pattern, $defaults, $requirements);
        $routeName = 'victoire_core_page_show_by_id';
        $collection->add($routeName, $route);
    }

    protected function addShowPageRoute(&$collection)
    {
        // prepare a new route
        $pattern = '/{url}';
        $defaults = array(
            '_controller' => 'VictoirePageBundle:Page:show',
        );
        $requirements = array(
            'url' => '^.*$',
        );
        $route = new Route($pattern, $defaults, $requirements);

        // add the new route to the route collection:
        $routeName = 'victoire_core_page_show';

        $collection->add($routeName, $route);
    }

    public function supports($resource, $type = null)
    {
        return $type === 'victoire';
    }
}
