<?php

namespace Victoire\Bundle\WidgetMapBundle\Helper;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetMapBundle\Exception\WidgetMapNotFoundException;

/**
 * @deprecated Just call $widget->getWidgetMap();
 *
 * Class WidgetMapHelper
 *
 */
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
        return $widget->getWidgetMap();
    }
}
