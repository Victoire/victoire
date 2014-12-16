<?php

namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Event\BusinessEntityAnnotationEvent;
use Victoire\Bundle\CoreBundle\Annotations\Reader\AnnotationDriver;
use Victoire\Bundle\WidgetBundle\Event\WidgetAnnotationEvent;

/**
 * Save victoire temp data in cache file
 */
class CacheSubscriber implements EventSubscriberInterface
{
    private $cache;

    public function __construct(AnnotationDriver $cache)
    {
        $this->cache = $cache;
    }

    public function addBusinessEntityInfo(BusinessEntityAnnotationEvent $event)
    {
        //Save business entity in cache
        $this->cacheBuilder->saveBusinessEntity($event->getBusinessEntity());
        //Add the business entity in widget cache entry
        foreach ($event->getWidgets() as $widget) {
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
     * bound to BusinessEntity or widget annotation load events
     *
     * @return array The subscribed events
     */
    public static function getSubscribedEvents()
    {
        return array(
            'victoire.business_entity_annotation_load' => 'addBusinessEntityClass',
            'victoire.widget_annotation_load'          => 'addWidgetInfo'
        );
    }
}
