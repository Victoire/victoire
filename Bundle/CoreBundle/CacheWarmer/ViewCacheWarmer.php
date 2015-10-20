<?php

namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Helper\ViewHelper;
use Victoire\Bundle\ViewReferenceBundle\Cache\Xml\ViewReferenceXmlCacheWriter;

/**
 * Called (for example on kernel request) to create the viewsReference cache file
 * ref. victoire_core.cache_warmer.view_warmer.
 */
class ViewCacheWarmer
{
    private $viewHelper;
    private $viewCacheWriter;

    /**
     * @param ViewHelper      $viewHelper      @victoire_page.page_helper
     * @param ViewReferenceXmlCacheWriter $viewCacheWriter @victoire_view_reference.cache.writer
     */
    public function __construct(ViewHelper $viewHelper, ViewReferenceXmlCacheWriter $viewCacheWriter, EntityManager $entityManager)
    {
        $this->viewHelper = $viewHelper;
        $this->viewCacheWriter = $viewCacheWriter;
        $this->entityManager = $entityManager;
    }

    /**
     * Warm the view cache file (if needed or force mode).
     *
     * @param string $cacheDir Where does the viewsReferences file should take place
     */
    public function warmUp($cacheDir)
    {
        if (!$this->viewCacheWriter->fileExists()) {
            $viewsReferences = $this->viewHelper->buildViewsReferences();
            $this->viewCacheWriter->write($viewsReferences);
        }
    }
}
