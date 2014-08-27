<?php

namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Victoire\Bundle\CoreBundle\Helper\ViewHelper;

class ViewCacheWarmer implements CacheWarmerInterface
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

    public function warmUp($cacheDir)
    {
        $viewsReferences = $this->viewHelper->getAllViewsReferences();
        $this->viewCacheHelper->write($viewsReferences);
    }

    public function isOptional()
    {
        return false;
    }
}
