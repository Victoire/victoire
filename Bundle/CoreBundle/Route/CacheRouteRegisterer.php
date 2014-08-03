<?php
namespace Victoire\Bundle\CoreBundle\Route;

/**
 * The cache route registerer
 */
class CacheRouteRegisterer
{
    protected $cache;

    /**
     * Constructor
     *
     * @param unknown $cache
     */
    public function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * write the route relative to this page in cache
     *
     * @param unknown $pages
     *
     * @return multitype:
     */
    public function registerRoutes($pages)
    {
        $routes = array();
        foreach ($pages as $page) {
            $routes = array_merge($routes, $this->registerRoute($page));
        }

        return $routes;
    }

    /**
     * write the route relative to this page in cache
     *
     * @return unknown|multitype:
     */
    public function getRoutes()
    {
        $routes = $this->cache->fetch('routes');

        if ($routes) {
            return $routes;
        }

        return array();
    }

    /**
     * write the route relative to this page in cache
     *
     * @param unknown $page
     *
     * @return multitype:multitype:multitype:
     */
    public function registerRoute($page)
    {
        $pageSlug = $page->getSlug();
        $routeName = "victoire_core_page_show";
        $slugs = array();
        $page = $page->getParent();

        while ($page) {
            array_push($slugs, $page->getSlug());
            $page = $page->getParent();
        }

        $slugs = array_reverse($slugs);
        $slugs[] = '{slug}';

        $routeName .= '_' . $pageSlug;

        $cachedRoutes = $this->cache->fetch('routes');

        if (!$cachedRoutes) {
            $cachedRoutes = array();
        }

        $params = array(
            'pattern' => $slugs
        );
        $route = array($routeName => $params);
        $routes = array_merge($cachedRoutes, $route);

        $this->cache->save('routes', $routes);

        return $route;
    }
}
