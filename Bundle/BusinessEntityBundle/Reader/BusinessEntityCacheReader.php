<?php

namespace Victoire\Bundle\BusinessEntityBundle\Reader;

use Victoire\Bundle\BusinessEntityBundle\Annotation\AnnotationDriver;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\CoreBundle\Cache\VictoireCache;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;

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
     * @param string $widgetName
     *
     * @return array
     */
    public function getReceiverProperties($widgetName)
    {
        $widgetMetadatas = $this->fetch('victoire_business_entity_widgets');

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
        $results = $this->cache->get($key, null);

        if (!$results) {
            //Reparse all entities to find some @VIC Annotation
            foreach ($this->driver->getAllClassNames() as $className) {
                $this->driver->parse(new \ReflectionClass($className));
            }
            $results = $this->cache->get($key, []);
        }

        return $results;
    }
}
