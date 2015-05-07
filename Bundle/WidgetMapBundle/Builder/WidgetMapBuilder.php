<?php

namespace Victoire\Bundle\WidgetMapBundle\Builder;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
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
        $widgetMap = array();

        //get the template widget map
        $template = $view->getTemplate();

        if ($template !== null) {
            $widgetMap = $this->build($template);
        }

        // build the view widgetMpas for each its slots
        foreach ($view->getSlots() as $slot) {
            if (empty($widgetMap[$slot->getId()])) {
                $widgetMap[$slot->getId()] = array();
            }

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
                            if ($reference != 0) {
                                if (isset($widgetMap[$slot->getId()])) {
                                    foreach ($widgetMap[$slot->getId()] as $key => $_widgetMap) {
                                        if ($_widgetMap->getWidgetId() === $reference) {
                                            $position += $_widgetMap->getPosition();
                                        }
                                    }
                                }
                            }

                            array_splice($widgetMap[$slot->getId()], $position - 1, 0, array($viewWidgetMap));
                            array_map(
                                function ($key, $_widgetMap) {
                                    $_widgetMap->setPosition($key + 1);
                                },
                                array_keys($widgetMap[$slot->getId()]),
                                $widgetMap[$slot->getId()]
                            );

                            break;
                        case WidgetMap::ACTION_OVERWRITE:
                            //parse the widget maps
                            $position = null;
                            foreach ($widgetMap[$slot->getId()] as $index => $wm) {
                                if ($wm->getWidgetId() == $viewWidgetMap->getReplacedWidgetId()) {
                                    //replace the widget map from the list
                                    unset($widgetMap[$slot->getId()][$index]);
                                    break;
                                }
                            }
                            array_splice($widgetMap[$slot->getId()], $viewWidgetMap->getPosition() - 1, 0, array($viewWidgetMap));
                            array_map(
                                function ($key, $_widgetMap) {
                                    $_widgetMap->setPosition($key + 1);
                                },
                                array_keys($widgetMap[$slot->getId()]),
                                $widgetMap[$slot->getId()]
                            );

                            break;
                        case WidgetMap::ACTION_DELETE:
                            //parse the widget maps
                            foreach ($widgetMap[$slot->getId()] as $index => $wm) {
                                if ($wm->getWidgetId() === $viewWidgetMap->getWidgetId()) {
                                    //remove the widget map from the list
                                    unset($widgetMap[$slot->getId()][$index]);
                                }
                            }
                            break;
                        default:
                            throw new \Exception('The action ['.$action.'] is not handeld yet.');
                            break;
                    }
                }
                ksort($widgetMap[$slot->getId()]);
            }

        }
        if ($updatePage) {
            $view->setBuiltWidgetMap($widgetMap);
        }

        return $widgetMap;
    }

}
