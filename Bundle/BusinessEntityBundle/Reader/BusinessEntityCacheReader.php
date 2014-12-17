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
        return $this->cache->fetch(BusinessEntity::CACHE_CLASSES, array());
    }
    /**
     * this method get annotated business classes (from cache if enabled)
     *
     * @return array $businessClasses
     **/
    public function getBusinessClassesForWidget(Widget $widget)
    {
        $widgetName = $this->widgetHelper->getWidgetName($widget);
        $businessClasses = $this->cache->fetch(BusinessEntity::CACHE_WIDGETS, array());

        return $businessClasses[$widgetName];
    }

    /**
     * Get a business entity by its id
     *
     * @param string $id
     *
     * @throws \Exception
     *
     * @return BusinessEntity
     */
    public function findById($id)
    {
        if ($id === null) {
            throw new \Exception('The parameter $id is mandatory');
        }

        //get all the business entities
        $businessEntities = $this->getBusinessClasses();

        //the result
        $businessEntity = null;

        //parse the business entities
        foreach ($businessEntities as $tempBusinessEntity) {
            //look for the same id
            if ($tempBusinessEntity->getId() === $id) {
                $businessEntity = $tempBusinessEntity;
                //business entity was found, there is no need to continue
                continue;
            }
        }

        return $businessEntity;
    }
}
