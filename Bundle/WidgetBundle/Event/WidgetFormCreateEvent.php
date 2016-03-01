<?php

namespace Victoire\Bundle\WidgetBundle\Event;

use Victoire\Bundle\WidgetBundle\Form\WidgetOptionsContainer;

class WidgetFormCreateEvent
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
     * WidgetFormBuildEvent constructor.
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
