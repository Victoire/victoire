<?php

namespace Victoire\Bundle\WidgetMapBundle\Builder;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetMapBundle\Helper\WidgetMapHelper;

/**
 * View WidgetMap builder
 *
 * ref: victoire_widget_map.builder
 */
class WidgetMapBuilder
{
    protected $helper;

    /**
     * Constructor
     *
     * @param WidgetMapHelper $helper Widget map helper
     */
    public function __construct(WidgetMapHelper $helper)
    {
        $this->helper = $helper;
    }

    public function build(View $view, $updatePage = true)
    {
        $viewWidgetMaps = null;
        $parentWidgetMap = array();
        $finalWidgetMap = array();

        //get the template widget map
        $template = $view->getTemplate();

        if ($template !== null) {
            $parentWidgetMap = $this->build($template);
        }

        // build the view widgetMpas for each its slots
        foreach ($view->getSlots() as $slot) {
            if (empty($parentWidgetMap[$slot->getId()])) {
                $parentWidgetMap[$slot->getId()] = array();
            }

            $widgetMap = array();
            if ($slot !== null) {
                $viewWidgetMaps = $slot->getWidgetMaps();
            }
            //if the current view have some widget maps
            if ($viewWidgetMaps !== null) {
                // $viewWidgetMaps = array_reverse($viewWidgetMaps, true);
                //we parse the widget maps
                foreach ($viewWidgetMaps as $viewWidgetMap) {
                    $viewWidgetMap = clone $viewWidgetMap;
                    //depending on the action
                    $action = $viewWidgetMap->getAction();

                    switch ($action) {
                        case WidgetMap::ACTION_CREATE:
                            $position = (int) $viewWidgetMap->getPosition();
                            $reference = (int) $viewWidgetMap->getPositionReference();
                            //the 0 reference means the top of the view
                            // if ($reference != 0) {
                            //     if (isset($parentWidgetMap[$slot->getId()])) {
                            //         foreach ($parentWidgetMap[$slot->getId()] as $key => $_widgetMap) {
                            //             if ($_widgetMap->getWidgetId() === $reference) {
                            //                 $position += $_widgetMap->getPosition();
                            //             }
                            //         }
                            //     }
                            // }

                            // $position = $this->helper->getNextAvailaiblePosition($position, $widgetMap);
                            // // $viewWidgetMap->setPosition($position);

                            // $widgetMap[$position - 1] = $viewWidgetMap;

                            if ($reference != 0) {
                                if (isset($parentWidgetMap[$slot->getId()])) {
                                    foreach ($parentWidgetMap[$slot->getId()] as $key => $_widgetMap) {
                                        if ($_widgetMap->getWidgetId() === $reference) {
                                            $position += $_widgetMap->getPosition();
                                        }
                                    }
                                }
                            }

                            array_splice($parentWidgetMap[$slot->getId()], $position - 1, 0, array($viewWidgetMap));
                            array_map(function ($key, $_widgetMap) {
                                    $_widgetMap->setPosition($key + 1);
                            },
                                array_keys($parentWidgetMap[$slot->getId()]),
                                $parentWidgetMap[$slot->getId()]);

                            break;
                        case WidgetMap::ACTION_OVERWRITE:
                            //parse the widget maps
                            foreach ($parentWidgetMap as $index => $wm) {
                                if ($wm->getWidgetId() === $viewWidgetMap->getReplacedWidgetId()) {
                                    //replace the widget map from the list
                                    $parentWidgetMap[$index] = $viewWidgetMap;
                                }
                            }
                            break;
                        case WidgetMap::ACTION_DELETE:
                            //parse the widget maps
                            foreach ($parentWidgetMap as $index => $wm) {
                                if ($wm->getWidgetId() === $viewWidgetMap->getWidgetId()) {
                                    //remove the widget map from the list
                                    unset($parentWidgetMap[$index]);
                                }
                            }
                            break;
                        default:
                            throw new \Exception('The action ['.$action.'] is not handeld yet.');
                            break;
                    }
                }
                ksort($parentWidgetMap[$slot->getId()]);
                // $finalWidgetMap[$slot->getId()] = $parentWidgetMap;
            }

        }
        //If the template of current view had widgets
        // if (null !== $parentWidgetMap) {
        //     // Iterate over the widgetmap we just built and detect if widgets are at the same position than parent's widgetmaps
        //     foreach ($finalWidgetMap as $_slotId => $_widgetMaps) {
        //         foreach ($_widgetMaps as $_position => $_widgetMap) {

        //             if (!empty($parentWidgetMap[$_slotId][$_position])) {
        //                 //insert the widgetmap at the computed position and move following widget
        //                 array_splice($parentWidgetMap[$_slotId], $_position, 0, array($_widgetMap));
        //             } else {
        //                 $parentWidgetMap[$_slotId][$_position] = $_widgetMap;
        //             }
        //         }
        //     }
        //     $finalWidgetMap = $parentWidgetMap;
        // }
        if ($updatePage) {
            $view->setBuiltWidgetMap($parentWidgetMap);
        }

        return $parentWidgetMap;
    }

}
