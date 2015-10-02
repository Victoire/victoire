<?php

namespace Victoire\Bundle\WidgetMapBundle\Helper;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;

/**
 * ref: victoire_widget_map.helper.
 */
class WidgetMapHelper
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

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
     * Guess the position of the widget relatively to the positionReference.
     *
     * @param Widget $widget            The widget to position
     * @param int    $positionReference Id of the parent widget
     *
     * @return WidgetMap The position of the widget
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
