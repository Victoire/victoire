<?php

namespace Victoire\Bundle\WidgetMapBundle\Builder;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetMapBundle\Resolver\WidgetMapChildrenResolver;
use Victoire\Bundle\WidgetMapBundle\Warmer\ContextualViewWarmer;

/**
 * View WidgetMap builder.
 *
 * ref: victoire_widget_map.builder
 */
class WidgetMapBuilder
{
    private $contextualViewWarmer;
    private $resolver;

    /**
     * WidgetMapBuilder constructor.
     *
     * @param ContextualViewWarmer      $contextualViewWarmer
     * @param WidgetMapChildrenResolver $resolver
     */
    public function __construct(
        ContextualViewWarmer $contextualViewWarmer,
        WidgetMapChildrenResolver $resolver
    ) {
        $this->contextualViewWarmer = $contextualViewWarmer;
        $this->resolver = $resolver;
    }

    /**
     * This method build widgetmaps relativly to given view and it's templates.
     *
     * @param View $view
     * @param bool $updatePage
     *
     * @return array
     */
    public function build(View $view, $updatePage = true)
    {
        $builtWidgetMap = [];

        $widgetMaps = $this->contextualViewWarmer->warm($view);

        $slots = $this->removeOverwritedWidgetMaps($widgetMaps);

        $this->removeDeletedWidgetMaps($slots);

        foreach ($slots as $slot => $slotWidgetMaps) {
            $mainWidgetMap = null;
            $builtWidgetMap[$slot] = [];

            $rootWidgetMap = $this->findRootWidgetMap($slotWidgetMaps);

            if ($rootWidgetMap) {
                $builtWidgetMap[$slot][] = $rootWidgetMap;
                $builtWidgetMap = $this->orderizeWidgetMap($rootWidgetMap, $builtWidgetMap, $slot, $slotWidgetMaps, $view);
            }
        }

        if ($updatePage) {
            $view->setBuiltWidgetMap($builtWidgetMap);
        }

        return $builtWidgetMap;
    }

    /**
     * This method takes the builtWidgetMap for view and creates an array that indicate, for
     * each widgetmap, if the position "after" and "before" are available.
     *
     * @param View $view
     *
     * @return array
     */
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

    /**
     * Get the children of given WidgetMap and place them recursively in the "builtWidgetMap" array at the right place
     * depending of the children parents and positions.
     *
     * @param WidgetMap $currentWidgetMap
     * @param $builtWidgetMap
     * @param $slot
     * @param $slotWidgetMaps
     * @param View $view
     *
     * @return mixed
     */
    protected function orderizeWidgetMap(WidgetMap $currentWidgetMap, $builtWidgetMap, $slot, $slotWidgetMaps, View $view)
    {
        $children = $this->resolver->getChildren($currentWidgetMap, $view);
        foreach ($children as $child) {
            // check if the founded child belongs to the view
            if (in_array($child, $slotWidgetMaps, true)) {
                // Find the position of the "currentWidgetMap" inside the builtWidgetMap,
                // add "1" to this position if wanted position is "after", 0 is it's before.
                $offset = array_search($currentWidgetMap, $builtWidgetMap[$slot]) + ($child->getPosition() == WidgetMap::POSITION_AFTER ? 1 : 0);
                // insert the child in builtWidgetMap at offset position
                array_splice($builtWidgetMap[$slot], $offset, 0, [$child]);
                // call myself with child
                $builtWidgetMap = $this->orderizeWidgetMap($child, $builtWidgetMap, $slot, $slotWidgetMaps, $view);
            }
        }

        return $builtWidgetMap;
    }

    /**
     * Create a $slot array that'll contain, for each slot, a widgetmap id as key and a widgetmap as value
     * Do not keeps widgetMaps that are overwrited.
     *
     * @param $widgetMaps
     *
     * @return array
     */
    protected function removeOverwritedWidgetMaps($widgetMaps)
    {
        $slots = [];
        /** @var WidgetMap $widgetMapItem */
        foreach ($widgetMaps as $widgetMapItem) {
            $id = $widgetMapItem->getId();
            // If the widgetmap replace one (has a "replaced"), use the replaced id as key
            if ($widgetMapItem->getReplaced()) {
                $id = $widgetMapItem->getReplaced()->getId();
            }
            // If "id" is not present in slot array or
            if (empty($slots[$widgetMapItem->getSlot()][$id])
                // or if the id exists AND the inserted widgetmap is overwrite|delete, then erase the initial widget by it
                || !empty($slots[$widgetMapItem->getSlot()][$id])
                && $widgetMapItem->getAction() !== WidgetMap::ACTION_CREATE) {
                $slots[$widgetMapItem->getSlot()][$id] = $widgetMapItem;
            }
        }

        return $slots;
    }

    /**
     * If "delete" widgetmaps are found, remove it because they're not rendered.
     *
     * @param $slots
     */
    protected function removeDeletedWidgetMaps(&$slots)
    {
        foreach ($slots as $slot => $widgetMaps) {
            foreach ($widgetMaps as $key => $widgetMap) {
                if ($widgetMap->getAction() == WidgetMap::ACTION_DELETE) {
                    unset($slots[$slot][$key]);
                }
            }
        }
    }

    /**
     * Find the "root" widgetmap (the one that has no parent).
     *
     * @param $widgetMaps
     *
     * @return WidgetMap|null
     */
    private function findRootWidgetMap($widgetMaps)
    {
        $rootWidgetMap = null;
        foreach ($widgetMaps as $_widgetMap) {
            if (!$_widgetMap->getParent()) {
                $rootWidgetMap = $_widgetMap;
                break;
            }
        }

        return $rootWidgetMap;
    }
}
