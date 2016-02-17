<?php

namespace Victoire\Bundle\WidgetMapBundle\Helper;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

class WidgetMapHelper
{
    /**
     * @param Widget $widget
     * @param View   $view
     */
    public static function getWidgetMapByWidgetAndView(Widget $widget, View $view)
    {
        foreach ($view->getBuiltWidgetMap() as $builtWidgetMap) {
            if (array_key_exists($widget->getId(), $builtWidgetMap)) {
                return $builtWidgetMap[$widget->getId()];
            }
        }

        return;
    }
}
