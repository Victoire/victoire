<?php
namespace Victoire\Bundle\CoreBundle\Route;

class CacheRouteRegisterer
{

    protected $cache;

    public function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * write the route relative to this page in cache
     * @param Page $pages
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
     */
    public function getRoutes()
    {
        if ($routes = $this->cache->fetch('routes')) {
            return $routes;
        }

        return array();
    }
    /**
     * write the route relative to this page in cache
     * @param Page $page
     */
    public function registerRoute($page)
    {
        $pageSlug = $page->getSlug();
        $routeName = "victoire_core_page_show";
        $slugs = array();
        while ($page = $page->getParent()) {
            array_push($slugs, $page->getSlug());
        }
        $slugs = array_reverse($slugs);
        $slugs[] = '{slug}';

        $routeName .= '_' . $pageSlug;
        if (!$cachedRoutes = $this->cache->fetch('routes')) {
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
