<?php

namespace Victoire\Bundle\WidgetBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\CoreBundle\Event\WidgetRenderEvent;
use Victoire\Bundle\WidgetBundle\Model\Widget;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;

/**
 * Class WidgetSubscriber
 *
 */
class WidgetSubscriber implements EventSubscriberInterface
{

    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return array(
            VictoireCmsEvents::WIDGET_PRE_RENDER => 'widgetPreRender',
        );
    }
    /**
     * This method prevent using :currentEntity parameter in a query for a BEPP
     *
     */
    public function widgetPreRender(WidgetRenderEvent $event)
    {
        $widget = $event->getWidget();
        if ($widget->getMode() == Widget::MODE_QUERY && $widget->getCurrentView() instanceof BusinessEntityPagePattern && strpos($widget->getQuery(), ":currentEntity") !== false) {
                $widget->setQuery('');
        }
    }
}
