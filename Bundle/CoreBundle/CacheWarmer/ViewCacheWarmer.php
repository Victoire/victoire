<?php

namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Helper\ViewHelper;
use Victoire\Bundle\ViewReferenceBundle\Cache\Xml\ViewReferenceXmlCacheDriver;

/**
 * Called (for example on kernel request) to create the viewsReference cache file
 * ref. victoire_core.cache_warmer.view_warmer.
 */
class ViewCacheWarmer
{
    private $viewHelper;
    private $viewCacheDriver;

    /**
     * @param ViewHelper                  $viewHelper      @victoire_page.page_helper
     * @param ViewReferenceXmlCacheDriver $viewCacheDriver @victoire_view_reference.cache.manager
     */
    public function __construct(ViewHelper $viewHelper, ViewReferenceXmlCacheDriver $viewCacheDriver, EntityManager $entityManager)
    {
        $this->viewHelper = $viewHelper;
        $this->viewCacheDriver = $viewCacheDriver;
        $this->entityManager = $entityManager;
    }

    /**
     * Warm the view cache file (if needed or force mode).
     *
     * @param string $cacheDir Where does the viewsReferences file should take place
     */
    public function warmUp($cacheDir)
    {
        if (!$this->viewCacheDriver->fileExists()) {
            $viewsReferences = $this->viewHelper->buildViewsReferences();
            $this->viewCacheDriver->writeFile($viewsReferences);
        }
    }
}
