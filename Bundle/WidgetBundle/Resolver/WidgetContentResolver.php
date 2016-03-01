<?php

namespace Victoire\Bundle\WidgetBundle\Resolver;

use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Resolver\Chain\WidgetContentResolverChain;

class WidgetContentResolver
{
    private $widgetContentResolverChain;

    public function __construct(WidgetContentResolverChain $widgetContentResolverChain)
    {
        $this->widgetContentResolverChain = $widgetContentResolverChain;
    }

    /**
     * Get content for the widget.
     *
     * @param Widget $widget
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getWidgetContent(Widget $widget)
    {
        //the mode of display of the widget
        $mode = $widget->getMode();

        //the widget must have a mode
        if ($mode === null) {
            throw new \Exception('The widget ['.$widget->getId().'] has no mode.');
        }

        $resolver = $this->widgetContentResolverChain->getResolverForWidget($widget);

        switch ($mode) {
            case Widget::MODE_STATIC:
                $parameters = $resolver->getWidgetStaticContent($widget);
                break;
            case Widget::MODE_ENTITY:
                //get the content of the widget with its entity
                $parameters = $resolver->getWidgetEntityContent($widget);
                break;
            case Widget::MODE_BUSINESS_ENTITY:
                //get the content of the widget with its entity
                $parameters = $resolver->getWidgetBusinessEntityContent($widget);
                break;
            case Widget::MODE_QUERY:
                $parameters = $resolver->getWidgetQueryContent($widget);
                break;
            default:
                throw new \Exception('The mode ['.$mode.'] is not supported by the widget manager. Widget ID:['.$widget->getId().']');
        }

        return $parameters;
    }
}
