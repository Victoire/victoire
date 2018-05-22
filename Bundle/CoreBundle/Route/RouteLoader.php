<?php

namespace Victoire\Bundle\CoreBundle\Route;

use Monolog\Logger;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader
{
    /**
     * @var array
     */
    protected $widgets;
    /**
     * @var Kernel
     */
    private $kernel;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * RouteLoader constructor.
     *
     * @param array  $widgets
     * @param Kernel $kernel
     * @param Logger $logger
     */
    public function __construct($widgets, Kernel $kernel, Logger $logger)
    {
        $this->widgets = $widgets;
        $this->kernel = $kernel;
        $this->logger = $logger;
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
        $bundles = [
            'VictoireAnalyticsBundle',
            'VictoireTemplateBundle',
            'VictoireTwigBundle',
            'VictoireBlogBundle',
            'VictoireBusinessPageBundle',
            'VictoireSeoBundle',
            'VictoireMediaBundle',
            'VictoirePageBundle',
            'VictoireCoreBundle',
            'VictoireConfigBundle',
            'VictoireWidgetBundle',
            'VictoireSitemapBundle',
        ];

        foreach ($bundles as $bundle) {
            try {
                $this->kernel->getBundle($bundle);
                $this->logger->addInfo('-> loading routes from '.$bundle);
                $resource = sprintf('@%s/Controller/', $bundle);
                $importedRoutes = $this->import($resource, 'annotation');
                $collection->addCollection($importedRoutes);
            } catch (\InvalidArgumentException $e) {
                $this->logger->addAlert($e->getMessage());
            }
        }
    }

    protected function addWidgetsRouting(RouteCollection &$collection)
    {
        foreach ($this->widgets as $widgetParams) {
            $controllerResource = sprintf('@VictoireWidget%sBundle/Controller/', $widgetParams['name']);
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
