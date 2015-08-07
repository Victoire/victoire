<?php

namespace Victoire\Bundle\CoreBundle\Cache\Builder;

use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\CoreBundle\Cache\ApcCache;

/**
 * Build victoire data entities
 * ref: victoire_core.cache_builder
 */
class CacheBuilder
{
    private $cache;

    public function __construct(ApcCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * save BusinessEntity
     */
    public function saveBusinessEntity(BusinessEntity $businessEntity)
    {
        $businessEntities = $this->cache->fetch(BusinessEntity::CACHE_CLASSES);
        $businessEntities[$businessEntity->getClass()] = $businessEntity;
        $this->cache->save(BusinessEntity::CACHE_CLASSES, $businessEntities);
    }

    /**
     * save Widget
     */
    public function saveWidgetReceiverProperties($widgetName, $receiverProperties)
    {
        $widgets = $this->cache->fetch(BusinessEntity::CACHE_WIDGETS, array());
        if (!array_key_exists($widgetName, $widgets)) {
            $widgets[$widgetName] = array();
        }

        $widgets[$widgetName]['receiverProperties'] = $receiverProperties;
        $this->cache->save(BusinessEntity::CACHE_WIDGETS, $widgets);
    }

    /**
     * add a BusinessEntity For Widget
     */
    public function addWidgetBusinessEntity($widgetName, $businessEntity)
    {
        $widgets = $this->cache->fetch(BusinessEntity::CACHE_WIDGETS, array());
        if (!array_key_exists($widgetName, $widgets)) {
            $widgets[$widgetName] = array('businessEntities' => array());
        }
        $widgets[$widgetName]['businessEntities'][$businessEntity->getName()] = $businessEntity;
        $this->cache->save(BusinessEntity::CACHE_WIDGETS, $widgets);
    }
}
