<?php

namespace Victoire\Bundle\WidgetMapBundle\Helper;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;

class WidgetMapHelper
{

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
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

    /**
     * Guess the position of the widget relatively to the positionReference
     *
     * @param Widget  $widget            The widget to position
     * @param integer $positionReference Id of the parent widget
     *
     * @return integer The position of the widget
     */
    public function generateWidgetPosition(WidgetMap $widgetMapEntry, $widget, $widgetMap, $positionReference)
    {
        $position = 1;
        $slotId = $widget->getSlot();

        if (empty($widgetMap[$slotId])) {
            $widgetMapEntry->setPosition($position);

            return $widgetMapEntry;
        }

        $slot = $widgetMap[$slotId];
        $referenceWidget = $this->em->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget')->findOneById($positionReference);

        //If we added a widget just after a parent widget
        //The position of the new widget is the one just after the parent widget
        if ($referenceWidget && $widget->getView() !== $referenceWidget->getView()) {
            $position = 1;
            $widgetMapEntry->setPosition($position);
            $widgetMapEntry->setPositionReference($positionReference);
        } else {

            foreach ($slot as $key => $_widgetMap) {
                if ($_widgetMap->getWidgetId() === (int) $positionReference) {
                    $widgetMapEntry->setPosition($_widgetMap->getPosition() + 1);
                    break;
                } elseif (0 === (int) $positionReference) {
                    $widgetMapEntry->setPosition(1);
                }
            }
        }

        return $widgetMapEntry;
    }

    /**
     * undocumented function
     *
     *
     * @return void
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

        // Iterate over slot's widgetMaps
        // foreach ($slot->getWidgetMaps() as $key => $_widgetMap) {

        //     // Handle positionReference
        //     // If $_widgetMap has same $positionReference (use "==" because we want "null" equals 0)
        //     // AND $_widgetMap's $position >= $widgetMapEntry's position
        //     // if ($_widgetMap->getPositionReference() == $widgetMapEntry->getPositionReference()
        //     if ($_widgetMap->getPosition() >= $widgetMapEntry->getPosition()) {
        //         // increment $_widgetMap's position
        //         $_widgetMap->setPosition($_widgetMap->getPosition() + 1);
        //         $slot->updateWidgetMap($_widgetMap);
        //     }
        // }

        $slot->addWidgetMap($widgetMapEntry);
        //update the widget map
        $view->updateWidgetMapBySlots();
    }
}
