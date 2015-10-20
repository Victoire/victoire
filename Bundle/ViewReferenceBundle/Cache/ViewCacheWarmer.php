<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Helper\ViewHelper;
use Victoire\Bundle\ViewReferenceBundle\Cache\Xml\ViewReferenceXmlCacheDriver;
use Victoire\Bundle\ViewReferenceBundle\Cache\Xml\ViewReferenceXmlCacheManager;

/**
 * Called (for example on kernel request) to create the viewsReference cache file
 * ref. victoire_view_reference.cache_warmer.
 */
class ViewCacheWarmer
{
    private $viewHelper;
    private $viewCacheDriver;
    private $viewCacheManager;

    /**
     * @param ViewHelper                  $viewHelper      @victoire_page.page_helper
     * @param ViewReferenceXmlCacheDriver $viewCacheDriver @victoire_view_reference.cache.driver
     * @param ViewReferenceXmlCacheManager $viewCacheManager @victoire_view_reference.cache.manager
     */
    public function __construct(
        ViewHelper $viewHelper,
        ViewReferenceXmlCacheDriver $viewCacheDriver,
        ViewReferenceXmlCacheManager $viewCacheManager,
        EntityManager $entityManager
    )
    {
        $this->viewHelper = $viewHelper;
        $this->viewCacheDriver = $viewCacheDriver;
        $this->viewCacheManager = $viewCacheManager;
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
            $this->viewCacheDriver->writeFile(
                $this->viewCacheManager->generateXml(
                    $this->viewHelper->buildViewsReferences()
                )
            );
        }
    }
}
