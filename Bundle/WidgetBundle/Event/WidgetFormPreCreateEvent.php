<?php

namespace Victoire\Bundle\WidgetBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\WidgetBundle\Form\WidgetOptionsContainer;

class WidgetFormPreCreateEvent extends Event
{
    /**
     * @var array
     */
    public $optionsContainer;
    
    /**
     * @var string
     */
    public $widgetFormTypeClass;

    /**
     * WidgetFormPreCreateEvent constructor.
     *
     * @param WidgetOptionsContainer $optionsContainer
     * @param string                 $widgetFormTypeClass
     */
    public function __construct(WidgetOptionsContainer $optionsContainer, $widgetFormTypeClass)
    {
        $this->optionsContainer = $optionsContainer;
        $this->widgetFormTypeClass = $widgetFormTypeClass;
    }
}
