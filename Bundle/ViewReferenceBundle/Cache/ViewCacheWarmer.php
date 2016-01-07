<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Helper\ViewHelper;
use Victoire\Bundle\ViewReferenceBundle\Cache\Redis\ViewReferenceRedisDriver;

/**
 * Called (for example on kernel request) to create the viewsReference cache file
 * ref. victoire_view_reference.cache_warmer.
 */
class ViewCacheWarmer
{
    private $viewHelper;
    private $viewRedisDriver;
    private $entityManager;

    /**
     * @param ViewHelper                   $viewHelper       @victoire_page.page_helper
     * @param ViewReferenceRedisDriver     $viewRedisDriver  @victoire_view_reference.redis.driver
     * @param EntityManager                $entityManager
     */
    public function __construct(
        ViewHelper $viewHelper,
        ViewReferenceRedisDriver $viewRedisDriver,
        EntityManager $entityManager
    ) {
        $this->viewHelper = $viewHelper;
        $this->viewRedisDriver = $viewRedisDriver;
        $this->entityManager = $entityManager;
    }

    /**
     * Warm the view cache file (if needed or force mode).
     *
     * @param string $cacheDir Where does the viewsReferences file should take place
     */
    public function warmUp($cacheDir)
    {
        if(!$this->viewRedisDriver->hasReference())
        {
            $this->viewRedisDriver->saveReferences(
                $this->viewHelper->buildViewsReferences()
            );
        }
    }
}
