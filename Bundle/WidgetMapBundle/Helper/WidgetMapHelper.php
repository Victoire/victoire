<?php

namespace Victoire\Bundle\WidgetMapBundle\Helper;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;

/**
 * View WidgetMap helper.
 * Some help functions for WidgetMap.
 *
 * ref: victoire_widget_map.helper.
 */
class WidgetMapHelper
{
    /**
     * Get the next availaible position for the widgetmap array.
     *
     * @param int   $position  The position required
     * @param array $widgetMap The list of widget map
     *
     * @return int The next position available
     */
    public function getNextAvailaiblePosition($position, $widgetMap)
    {
        //if the position is not available
        if (isset($widgetMap[$position])) {
            //we increment the position
            $position += 1;
            //we check that this one is also available
            $position = $this->getNextAvailaiblePosition($position, $widgetMap);
        }

        return $position;
    }

    /**
     * undocumented function.
     *
     *
     * @param \Victoire\Bundle\CoreBundle\Entity\View $view
     *
     * @return void
     *
     * @author
     **/
    public function insertWidgetMapInSlot($slotId, WidgetMap $widgetMapEntry, $view)
    {
        //get the slot
        $slot = $view->getSlotById($slotId);

        //test that slot exists
        if ($slot === null) {
            $slot = new Slot();
            $slot->setId($slotId);
            $view->addSlot($slot);
        }

        $slot->addWidgetMap($widgetMapEntry);
        //update the widget map
        $view->updateWidgetMapBySlots();
    }

    /**
     * Find a widgetMap by widgetId and view.
     *
     * @param int  $widgetId
     * @param View $view
     *
     * @return WidgetMap
     **/
    public function removeWidgetMapByWidgetIdAndView($widgetId, View $view)
    {
        foreach ($view->getSlots() as $_slot) {
            foreach ($_slot->getWidgetMaps() as $_widgetMap) {
                if ($_widgetMap->getWidgetId() == $widgetId) {
                    $_slot->removeWidgetMap($_widgetMap);
                    //update the widget map
                    $view->updateWidgetMapBySlots();

                    return [
                        'success'  => true,
                    ];
                }
            }
        }

        return [
            'success'  => false,
            'message'  => 'The widget isn\'t present in widgetMap...',
        ];
    }
}
