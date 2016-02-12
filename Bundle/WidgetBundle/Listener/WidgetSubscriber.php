<?php

namespace Victoire\Bundle\WidgetBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Event\WidgetRenderEvent;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * Class WidgetSubscriber.
 */
class WidgetSubscriber implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            VictoireCmsEvents::WIDGET_PRE_RENDER => 'widgetPreRender',
        ];
    }

    /**
     * This method prevent using :currentEntity parameter in a query for a BEPP.
     */
    public function widgetPreRender(WidgetRenderEvent $event)
    {
        $widget = $event->getWidget();
        if ($widget->getMode() == Widget::MODE_QUERY && $widget->getCurrentView() instanceof BusinessTemplate && strpos($widget->getQuery(), ':currentEntity') !== false) {
            $widget->setQuery('');
        }
    }
}
