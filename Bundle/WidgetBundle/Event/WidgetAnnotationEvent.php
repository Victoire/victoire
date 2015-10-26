<?php

namespace Victoire\Bundle\WidgetBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 *
 */
class WidgetAnnotationEvent extends Event
{
    private $widgetName;
    private $receiverProperties;

    public function __construct($widgetName, $receiverProperties)
    {
        $this->widgetName = $widgetName;
        $this->receiverProperties = $receiverProperties;
    }

    /**
     * Get widget name.
     *
     * @return string
     */
    public function getWidgetName()
    {
        return $this->widgetName;
    }

    /**
     * Get receiverProperties.
     *
     * @return array
     */
    public function getReceiverProperties()
    {
        return $this->receiverProperties;
    }
}
