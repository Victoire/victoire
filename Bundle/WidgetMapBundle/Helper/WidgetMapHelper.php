<?php

namespace Victoire\Bundle\WidgetMapBundle\Helper;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetMapBundle\Exception\WidgetMapNotFoundException;

class WidgetMapHelper
{
    /**
     * @param Widget $widget
     * @param View   $view
     *
     * @return WidgetMap|WidgetMapNotFoundException
     */
    public static function getWidgetMapByWidgetAndView(Widget $widget, View $view)
    {
        foreach ($view->getBuiltWidgetMap() as $builtWidgetMap) {
            if (array_key_exists($widget->getId(), $builtWidgetMap)) {
                return $builtWidgetMap[$widget->getId()];
            }
        }

        return new WidgetMapNotFoundException(sprintf(
            'Cannot find the widget #%s in the view#%s widget map', $widget->getId(), $view->getId()
        ));
    }
}
