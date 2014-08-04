<?php
namespace Victoire\Bundle\WidgetBundle\Resolver;

use Victoire\Bundle\WidgetBundle\Model\Widget;

abstract class BaseWidgetContentResolver
{

    /**
     * Get the static content of the widget
     *
     * @param Widget $widget
     *
     * @return string
     */
    public function getWidgetStaticContent(Widget $widget)
    {
        return '';
    }

    /**
     * Get the business entity content
     * @param Widget $widget
     *
     * @return string
     */
    public function getWidgetBusinessEntityContent(Widget $widget)
    {
        return '';
    }

    /**
     * Get the content of the widget by the entity linked to it
     *
     * @param Widget $widget
     *
     * @return string
     *
     */
    public function getWidgetEntityContent(Widget $widget)
    {
        return '';
    }

    /**
     * Get the content of the widget for the query mode
     *
     * @param Widget $widget
     *
     * @return string
     *
     */
    public function getWidgetQueryContent(Widget $widget)
    {
        return '';
    }
}
