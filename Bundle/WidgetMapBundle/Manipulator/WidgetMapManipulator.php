<?php

namespace Victoire\Bundle\WidgetMapBundle\Manipulator;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * View WidgetMap manipulator.
 * This class allow to manipulate a WidgetMap by using the entity manager.
 *
 * ref: victoire_widget_map.builder
 */
class WidgetMapManipulator
{

    /**
     * Guess the position of the widget relatively to the positionReference.
     *
     * @param Widget $widget The widget to position
     * @param int $positionReference Id of the parent widget
     *
     * @return WidgetMap The position of the widget
     */
    public function generateWidgetPosition(EntityManager $entityManager, WidgetMap $widgetMapEntry, $widget, $widgetMap, $positionReference)
    {
        $position = 1;
        $slotId = $widget->getSlot();

        if (empty($widgetMap[$slotId])) {
            $widgetMapEntry->setPosition($position);

            return $widgetMapEntry;
        }

        $slot = $widgetMap[$slotId];
        $referenceWidget = $entityManager->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget')->findOneById(
            $positionReference
        );

        //If we added a widget just after a parent widget
        //The position of the new widget is the one just after the parent widget
        if ($referenceWidget && $widget->getView() !== $referenceWidget->getView()) {
            $position = 1;
            $widgetMapEntry->setPosition($position);
            $widgetMapEntry->setPositionReference($positionReference);
        } else {
            foreach ($slot as $key => $_widgetMap) {
                if ($_widgetMap->getWidgetId() === (int)$positionReference) {
                    $widgetMapEntry->setPosition($_widgetMap->getPosition() + 1);
                    break;
                } elseif (0 === (int)$positionReference) {
                    $widgetMapEntry->setPosition(1);
                }
            }
        }

        return $widgetMapEntry;
    }
}