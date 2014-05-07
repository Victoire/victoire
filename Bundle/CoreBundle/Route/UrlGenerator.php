<?php
namespace Victoire\Bundle\CoreBundle\Route;

use Symfony\Component\Routing\Generator\UrlGenerator as BaseUrlGenerator;
use Victoire\Bundle\CoreBundle\Cache\ApcCache;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Psr\Log\LoggerInterface;


class UrlGenerator extends BaseUrlGenerator
{

    protected $cache;

    /**
     * Constructor.
     *
     * @param ApcCache             $cache   The custom ApcCache
     * @param RouteCollection      $routes  A RouteCollection instance
     * @param RequestContext       $context The context
     * @param LoggerInterface|null $logger  A logger instance
     *
     * @api
     */
    public function __construct(ApcCache $cache, RouteCollection $routes, RequestContext $context, LoggerInterface $logger = null)
    {
        $this->cache = $cache;
        $this->routes = $routes;
        $this->context = $context;
        $this->logger = $logger;
    }


}
