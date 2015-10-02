<?php

namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Event\BusinessEntityAnnotationEvent;
use Victoire\Bundle\CoreBundle\Cache\Builder\CacheBuilder;
use Victoire\Bundle\WidgetBundle\Event\WidgetAnnotationEvent;

/**
 * Save victoire temp data in cache file
 * ref: victoire_core.cache_subscriber.
 */
class CacheSubscriber implements EventSubscriberInterface
{
    private $cacheBuilder;

    public function __construct(CacheBuilder $cacheBuilder)
    {
        $this->cacheBuilder = $cacheBuilder;
    }

    public function addBusinessEntityInfo(BusinessEntityAnnotationEvent $event)
    {
        //Save business entity in cache
        $this->cacheBuilder->saveBusinessEntity($event->getBusinessEntity());
        //Add the business entity in widget cache entry
        foreach ($event->getWidgets() as $widget) {
            if (is_array($widget)) {
                $widget = $widget[0];
            }
            $this->cacheBuilder->addWidgetBusinessEntity($widget, $event->getBusinessEntity());
        }
    }

    /**
     *
     */
    public function addWidgetInfo(WidgetAnnotationEvent $event)
    {
        //save widget receiver properties in cache
        $this->cacheBuilder->saveWidgetReceiverProperties($event->getWidgetName(), $event->getReceiverProperties());
    }

    /**
     * bound to BusinessEntity or widget annotation load events.
     *
     * @return array The subscribed events
     */
    public static function getSubscribedEvents()
    {
        return [
            'victoire.business_entity_annotation_load' => 'addBusinessEntityInfo',
            'victoire.widget_annotation_load'          => 'addWidgetInfo',
        ];
    }
}
