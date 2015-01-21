<?php

namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Victoire\Bundle\CoreBundle\Helper\ViewHelper;

/**
 * Called (for example on kernel request) to create the viewsReference cache file
 * ref. victoire_core.cache_warmer.view_warmer
 */
class ViewCacheWarmer
{
    private $viewHelper;
    private $viewCacheHelper;

    /**
     * @param ViewHelper      $viewHelper      @victoire_page.page_helper
     * @param ViewCacheHelper $viewCacheHelper @victoire_core.view_cache_helper
     */
    public function __construct(ViewHelper $viewHelper, ViewCacheHelper $viewCacheHelper)
    {
        $this->viewHelper = $viewHelper;
        $this->viewCacheHelper = $viewCacheHelper;
    }

    /**
     * Warm the view cache file (if needed or force mode)
     * @param string  $cacheDir Where does the viewsReferences file should take place
     * @param boolean $force    Should we recompute the file if the file already exists ?
     */
    public function warmUp($cacheDir)
    {
        if (!$this->viewCacheHelper->fileExists()) {
            $viewsReferences = $this->viewHelper->getAllViewsReferences();
            $this->viewCacheHelper->write($viewsReferences);
        }
    }
}
