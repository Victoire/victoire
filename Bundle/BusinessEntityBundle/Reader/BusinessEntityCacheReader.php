<?php
namespace Victoire\Bundle\BusinessEntityBundle\Reader;

use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\CoreBundle\Cache\ApcCache;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;
use Victoire\Bundle\WidgetBundle\Model\Widget;

/**
 * The BusinessEntityHelper
 *
 * ref: victoire_business_entity.cache_reader
 */
class BusinessEntityCacheReader
{
    protected $cache;
    protected $widgetHelper;

    /**
     * Constructor
     * @param ApcCache     $cache
     * @param WidgetHelper $widgetHelper
     *
     */
    public function __construct(ApcCache $cache, WidgetHelper $widgetHelper)
    {
        $this->cache = $cache;
        $this->widgetHelper = $widgetHelper;
    }

    /**
     * this method get annotated business classes (from cache if enabled)
     *
     * @return array $businessClasses
     **/
    public function getBusinessClasses()
    {
        $businessClasses = $this->cache->fetch(BusinessEntity::CACHE_CLASSES, array());

        return $businessClasses;
    }
    /**
     * this method get annotated business classes (from cache if enabled)
     *
     * @return array $businessClasses
     **/
    public function getBusinessClassesForWidget(Widget $widget)
    {
        $widgetName = $this->widgetHelper->getWidgetName($widget);

        $businessClassesForWidget = $this->cache->fetch(BusinessEntity::CACHE_WIDGETS, array());
        var_dump($businessClassesForWidget);exit;

        return $businessClassesForWidget[$widgetName];
    }
}
