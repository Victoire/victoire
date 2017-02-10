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
     * @param View      $viewToWarm
     * @param View|null $contextualView Used in recursive call only
     *
     * @return WidgetMap[]
     */
    public function warm(View $viewToWarm, View $contextualView = null)
    {
        $widgetMaps = [];

        if (null === $contextualView) {
            $contextualView = $viewToWarm;
        }

        foreach ($viewToWarm->getWidgetMaps() as $_widgetMap) {
            $_widgetMap->setContextualView($contextualView);
            $widgetMaps[] = $_widgetMap;
        }

        if ($template = $viewToWarm->getTemplate()) {
            $templateWidgetMaps = $this->warm($template, $contextualView);
            $widgetMaps = array_merge($widgetMaps, $templateWidgetMaps);
        }

        return $widgetMaps;
    }
}
