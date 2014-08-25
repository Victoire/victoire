<?php
namespace Victoire\Bundle\PageBundle\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Victoire\Bundle\PageBundle\Helper\PageCacheHelper;
use Victoire\Bundle\PageBundle\Helper\PageHelper;

class PageCacheWarmer implements CacheWarmerInterface
{
    private $pageHelper;
    private $pageCacheHelper;

    /**
     * @param PageHelper      $pageHelper      @victoire_page.page_helper
     * @param PageCacheHelper $pageCacheHelper @victoire_page.page_cache_helper
     */
    public function __construct(PageHelper $pageHelper, PageCacheHelper $pageCacheHelper)
    {
        $this->pageHelper = $pageHelper;
        $this->pageCacheHelper = $pageCacheHelper;
    }
    public function warmUp($cacheDir)
    {
        $pages = $this->pageHelper->getAllPages();
        $this->pageCacheHelper->writeCache($pages);
    }

    public function isOptional()
    {
        return false;
    }
}
