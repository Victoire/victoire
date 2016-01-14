<?php

namespace Victoire\Bundle\WidgetMapBundle\Builder;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

/**
 * View WidgetMap builder.
 *
 * ref: victoire_widget_map.builder
 */
class WidgetMapBuilder
{

    /**
     * @param View $view
     * @param EntityManager $em
     * @param bool $updatePage
     * @return array
     */
    public function build(View $view, EntityManager $em = null, $updatePage = true)
    {
        $widgetMaps = $view->getWidgetMaps()->toArray();
        $template = clone $view;
        $builtWidgetMap = [];

        while (null !== $template = $template->getTemplate()) {
            $widgetMaps = array_merge($widgetMaps, $template->getWidgetMaps()->toArray());
        }
        $slots = [];
        /** @var WidgetMap $widgetMapItem */
        foreach ($widgetMaps as $widgetMapItem) {
            $id = $widgetMapItem->getId();
            if ($widgetMapItem->getReplaced()) {
                $id = $widgetMapItem->getReplaced()->getId();
            }
            if (empty($slots[$widgetMapItem->getSlot()][$id]) || !empty($slots[$widgetMapItem->getSlot()][$id]) && $slots[$widgetMapItem->getSlot()][$id]->getAction() !== WidgetMap::ACTION_OVERWRITE) {
                $slots[$widgetMapItem->getSlot()][$id] = $widgetMapItem;
            }
        }

        foreach ($slots as $slot => $widgetMaps) {
            $mainWidgetMap = null;
            $builtWidgetMap[$slot] = [];

            /** @var WidgetMap $_widgetMap */
            foreach ($widgetMaps as $_widgetMap) {
                if (!$_widgetMap->getParent()) {
                    $mainWidgetMap = $_widgetMap;
                    break;
                }
            }

            if ($mainWidgetMap) {
                $builtWidgetMap[$slot][] = $mainWidgetMap;
                /**
                 * @param WidgetMap $currentWidgetMap
                 */
                $orderizeWidgetMap = function ($currentWidgetMap, $builtWidgetMap) use ($slot, &$orderizeWidgetMap, $widgetMaps) {
                    $children = $currentWidgetMap->getChildren();
                    foreach ($children as $child) {
                        if (in_array($child, $widgetMaps)) {
                            $offset = array_search($currentWidgetMap, $builtWidgetMap[$slot]) + ($child->getPosition() == WidgetMap::POSITION_AFTER ? 1 : 0);
                            array_splice($builtWidgetMap[$slot], $offset, 0, [$child]);
                            $builtWidgetMap = $orderizeWidgetMap($child, $builtWidgetMap);
                        }
                    }

                    return $builtWidgetMap;
                };
                $builtWidgetMap = $orderizeWidgetMap($mainWidgetMap, $builtWidgetMap);
            }
        }

        if ($updatePage) {
            $view->setBuiltWidgetMap($builtWidgetMap);
        }

        return $builtWidgetMap;
    }

    public function getAvailablePosition(View $view)
    {
        $widgetMaps = $view->getBuiltWidgetMap();

        $availablePositions = [];
        foreach ($widgetMaps as $slot => $widgetMap) {
            foreach ($widgetMap as $_widgetMap) {
                $availablePositions[$slot][$_widgetMap->getId()]['id'] = $_widgetMap->getId();
                $availablePositions[$slot][$_widgetMap->getId()][WidgetMap::POSITION_BEFORE] = true;
                $availablePositions[$slot][$_widgetMap->getId()][WidgetMap::POSITION_AFTER] = true;
                if ($_widgetMap->getReplaced()) {
                    $availablePositions[$slot][$_widgetMap->getId()]['replaced'] = $_widgetMap->getReplaced()->getId();
                }
            }
            /** @var WidgetMap $_widgetMap */
            foreach ($widgetMap as $_widgetMap) {
                if ($_widgetMap->getParent()) {
                    if ($substitute = $_widgetMap->getParent()->getSubstituteForView($view)) {
                        $availablePositions[$slot][$substitute->getId()][$_widgetMap->getPosition()] = false;
                    } else {
                        $availablePositions[$slot][$_widgetMap->getParent()->getId()][$_widgetMap->getPosition()] = false;
                    }
                }
            }
        }

        return $availablePositions;


    }
}
