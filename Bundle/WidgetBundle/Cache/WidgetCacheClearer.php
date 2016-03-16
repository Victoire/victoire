<?php

namespace Victoire\Bundle\WidgetBundle\Cache;

use Predis\Client;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Victoire\Bundle\CoreBundle\Cache\VictoireCache;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Entity\WidgetSlotInterface;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;

/**
 * This class handle the saving of Widgets.
 * Widgets are stored for a week, but are invalidated as soon as
 * the Widget's or BusinessEntity's updatedAt field is changed.
 */
class WidgetCacheClearer implements CacheClearerInterface
{
    /**
     * @var Client
     */
    private $cache;
    /**
     * WidgetCache constructor.
     *
     * @param Client               $cache
     */
    public function __construct(WidgetCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param Widget $widget
     *
     * @return string
     */
    public function clear($cacheDir)
    {
        $this->cache->clear();
    }
}
