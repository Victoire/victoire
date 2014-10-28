<?php

namespace Victoire\Bundle\WidgetMapBundle\Helper;

use Doctrine\ORM\EntityManager;

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
    public function generateWidgetPosition(&$widgetMapEntry, $widget, $widgetMap, $positionReference)
    {
        $position = 0;
        $slotId = $widget->getSlot();

        $slot = $widgetMap[$slotId];
        $referenceWidget = $this->em->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget')->findOneById($positionReference);

        if ($referenceWidget && $widget->getView() !== $referenceWidget->getView()) {
            $position = 1;
            $widgetMapEntry->setPosition($position);
            $widgetMapEntry->setPositionReference($referenceWidget->getId());
        } else {
            foreach ($slot as $key => $_widgetMap) {

                if ($_widgetMap->getWidgetId() === (int) $positionReference) {
                    $position = $_widgetMap->getPosition() + 1;
                    $widgetMapEntry->setPosition($position);
                    $widgetMapEntry->setPositionReference($positionReference);
                    break;
                }
            }
        }

        return $position;
    }
}
