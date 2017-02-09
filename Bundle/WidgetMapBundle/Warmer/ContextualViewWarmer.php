<?php

namespace Victoire\Bundle\WidgetMapBundle\Warmer;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

/**
 * ContextualViewWarmer.
 *
 * Ref: victoire_widget_map.contextual_view_warmer
 */
class ContextualViewWarmer
{
    /**
     * Give a contextual View to each WidgetMap used in a View and its Templates.
     *
     * @param View $view
     *
     * @return WidgetMap[]
     */
    public function warm(View $view)
    {
        $widgetMaps = [];

        foreach ($view->getWidgetMaps() as $_widgetMap) {
            $_widgetMap->setContextualView($view);
            $widgetMaps[] = $_widgetMap;
        }

        if ($template = $view->getTemplate()) {
            $templateWidgetMaps = $this->warm($template);
            $widgetMaps = array_merge($widgetMaps, $templateWidgetMaps);
        }

        return $widgetMaps;
    }
}
