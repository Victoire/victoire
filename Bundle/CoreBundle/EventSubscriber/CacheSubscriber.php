<?php

namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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

    /**
     * Save widget receiver properties in cache.
     *
     * @param WidgetAnnotationEvent $event
     */
    public function addWidgetInfo(WidgetAnnotationEvent $event)
    {
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
            'victoire.widget_annotation_load' => 'addWidgetInfo',
        ];
    }
}
