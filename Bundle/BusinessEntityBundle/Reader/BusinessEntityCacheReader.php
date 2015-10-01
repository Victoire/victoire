<?php

namespace Victoire\Bundle\BusinessEntityBundle\Reader;

use Victoire\Bundle\BusinessEntityBundle\Annotation\AnnotationDriver;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\CoreBundle\Cache\VictoireCache;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;
use Victoire\Bundle\WidgetBundle\Model\Widget;

/**
 * The BusinessEntity Cache Reader.
 *
 * ref: victoire_business_entity.cache_reader
 */
class BusinessEntityCacheReader
{
    protected $cache;
    protected $widgetHelper;
    protected $driver; // @victoire_business_entity.annotation_driver

    /**
     * Constructor.
     *
     * @param VictoireCache    $cache
     * @param WidgetHelper     $widgetHelper
     * @param AnnotationDriver $driver       If cache returns empty results, we try to refectch data
     */
    public function __construct(VictoireCache $cache, WidgetHelper $widgetHelper, AnnotationDriver $driver)
    {
        $this->cache = $cache;
        $this->widgetHelper = $widgetHelper;
        $this->driver = $driver;
    }

    /**
     * this method get annotated business classes (from cache if enabled).
     *
     * @return array $businessClasses
     **/
    public function getBusinessClasses()
    {
        $businessClasses = $this->fetch(BusinessEntity::CACHE_CLASSES);

        return $businessClasses;
    }

    /**
     * this method get annotated business classes (from cache if enabled).
     *
     * @param Widget $widget
     *
     * @return array $businessClasses
     */
    public function getBusinessClassesForWidget(Widget $widget)
    {
        $widgetName = $this->widgetHelper->getWidgetName($widget);
        $widgetMetadatas = $this->fetch(BusinessEntity::CACHE_WIDGETS);
        if (isset($widgetMetadatas[$widgetName]) && array_key_exists('businessEntities', $widgetMetadatas[$widgetName])) {
            return $widgetMetadatas[$widgetName]['businessEntities'];
        }

        return [];
    }

    /**
     * @param string $namespace
     */
    public function getBusinessProperties($namespace)
    {
        /** @var BusinessEntity[] $widgetMetadatas */
        $widgetMetadatas = $this->fetch(BusinessEntity::CACHE_CLASSES);

        if (isset($widgetMetadatas[$namespace])) {
            return $widgetMetadatas[$namespace]->getBusinessProperties();
        }

        return [];
    }

    /**
     * @param string $widgetName
     *
     * @return array
     */
    public function getReceiverProperties($widgetName)
    {
        $widgetMetadatas = $this->fetch(BusinessEntity::CACHE_WIDGETS);

        if (isset($widgetMetadatas[$widgetName]) && array_key_exists('receiverProperties', $widgetMetadatas[$widgetName])) {
            return $widgetMetadatas[$widgetName]['receiverProperties'];
        }

        return [];
    }

    /**
     * Fetch in Cache system and try to reparse Annotation if no results.
     *
     * @param $key
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     *
     * @return mixed
     */
    protected function fetch($key)
    {
        $results = $this->cache->fetch($key, null);

        if (!$results) {
            //Reparse all entities to find some @VIC Annotation
            foreach ($this->driver->getAllClassNames() as $className) {
                $this->driver->parse(new \ReflectionClass($className));
            }
            $results = $this->cache->fetch($key, []);
        }

        return $results;
    }

    /**
     * Get a business entity by its id.
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
