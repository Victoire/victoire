<?php

namespace Victoire\Bundle\WidgetMapBundle\Helper;

class WidgetMapHelper
{

    /**
     * Get the next availaible position for the widgetmap array
     *
     * @param integer $position  The position required
     * @param array   $widgetMap The list of widget map
     *
     * @return integer The next position available
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
}
