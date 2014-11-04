<?php

namespace Victoire\Bundle\WidgetMapBundle\Manager;

use Doctrine\Orm\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetBundle\Model\Widget;

class WidgetMapManager
{

    private $em;
    private $builder;

    public function __construct(EntityManager $em, $builder)
    {
        $this->em = $em;
        $this->builder = $builder;
    }
    /**
     * compute the widget map for view
     * @param View  $view
     * @param array $sortedWidgets
     *
     * @todo Be able to move a widget from a slot to another
     * @todo test if the widget is allowed for the given slot
     *
     * @throws Exception
     */
    public function updateWidgetMapOrder(View $view, $sortedWidgets)
    {
        $widgetSlots = array();

        //parse the sorted widgets
        foreach ($sortedWidgets as $slotId => $widgetContainers) {

            //create an array for this slot
            $widgetSlots[$slotId] = array();

            //parse the list of div ids
            foreach ($widgetContainers as $widgetId) {

                if ($widgetId === '' || $widgetId === null) {
                    throw new \Exception('The containerId does not have any numerical characters. Containerid:['.$containerId.']');
                }

                //add the id of the widget to the slot
                //cast the id as integer
                $widgetSlots[$slotId][] = intval($widgetId);
            }
        }

        $this->updateWidgetMapsFromView($view, $widgetSlots);
        $view->updateWidgetMapBySlots();

        //update the view with the new widget map
        $this->em->persist($view);
        $this->em->flush();
    }

    /**
     * Delete the widget from the view
     *
     * @param View   $view
     * @param Widget $widget
     *
     * @throws \Exception The slot does not exists
     */
    public function deleteWidgetFromView(View $view, Widget $widget)
    {
        //the widget view
        $widgetView = $widget->getView();

        //the widget slot
        $widgetSlotId = $widget->getSlot();

        //the widget id
        $widgetId = $widget->getId();

        //get the slot
        $slot = $view->getSlotById($widgetSlotId);

        //we remove the widget from the current view
        if ($widgetView === $view) {
            //test that the slot for the widget exists
            if ($slot === null) {
                throw new \Exception('The slot['.$widgetSlotId.'] for the widget ['.$widgetId.'] of the view ['.$view->getId().'] was not found.');
            }

            //get the widget map
            $widgetMap = $slot->getWidgetMapByWidgetId($widgetId);

            //check that the widget map exists
            if ($widgetMap === null) {
                throw new \Exception('The widgetMap for the widget ['.$widgetId.'] and the view ['.$view->getId().'] does not exists.');
            }

            //remove the widget map from the slot
            $slot->removeWidgetMap($widgetMap);
        } else {
            //there might be no slot yet for the child view
            if ($slot === null) {
                //create a new slot
                $slot = new Slot();
                $slot->setId($widgetSlotId);

                //add the new slot to the view
                $view->addSlot($slot);
            }

            //the widget is owned by another view (a parent)
            //so we add a new widget map that indicates we delete this widget
            $widgetMap = new WidgetMap();
            $widgetMap->setAction(WidgetMap::ACTION_DELETE);
            $widgetMap->setWidgetId($widgetId);

            $slot->addWidgetMap($widgetMap);
        }
    }

    /**
     * Get the slots for the view by the sorted slots given by the sortable js script when ordering widgets
     *
     * @param View  $view
     * @param array $widgetSlots
     */
    protected function updateWidgetMapsFromView(View $view, $widgetSlots)
    {
        foreach ($widgetSlots as $slotId => $widgetIds) {
            //the reference to the previous widget map parent
            $lastParentWidgetMapId = null;

            //get the slot of the view
            $slot = $view->getSlotById($slotId);

            //test that slot exists or create it, it could not exists if no widget has been created inside yet
            if ($slot === null) {
                $slot = new Slot();
                $slot->setId($slotId);
                $view->addSlot($slot);
            }

            //init the widget map position counter
            $positionCounter = 1;

            //parse the widget ids
            foreach ($widgetIds as $widgetId) {
                //get the initial widget map of this widget
                $widgetMap = $slot->getWidgetMapByWidgetId($widgetId);

                //the slot comes from the parent
                if ($widgetMap === null) {
                    $lastParentWidgetMapId = $widgetId;
                    //we reset the widget map position counter
                    $positionCounter = 1;
                } else {
                    //the parent widget map reference
                    if ($lastParentWidgetMapId === null) {
                        $reference = 0;
                    } else {
                        $reference = $lastParentWidgetMapId;
                    }
                    $widgetMap->setPositionReference($reference);
                    //update the position
                    $widgetMap->setPosition($positionCounter);
                    //incremente the position widget map counter
                    $positionCounter++;
                }
            }
        }
    }

    /**
     * create a widgetMap for the new Widget cloned
     *
     * @return void
     **/
    public function overwriteWidgetMap(Widget $widgetCopy, Widget $widget, Slot $slot, View $view)
    {

        //the id of the new widget
        $widgetId = $widgetCopy->getId();

        //the widget slot
        $widgetSlotId = $widget->getSlot();

        //the widget id
        $replacedWidgetId = $widget->getId();

        //get the slot
        $slot = $view->getSlotById($widgetSlotId);

        //there might be no slot yet for the child view
        if ($slot === null) {
            //create a new slot
            $slot = new Slot();
            $slot->setId($widgetSlotId);

            //add the new slot to the view
            $view->addSlot($slot);
        }

        //the widget is owned by another view (a parent)
        //so we add a new widget map that indicates we delete this widget
        $widgetMap = new WidgetMap();
        $widgetMap->setAction(WidgetMap::ACTION_OVERWRITE);
        $widgetMap->setReplacedWidgetId($replacedWidgetId);
        $widgetMap->setWidgetId($widgetId);

        $slot->addWidgetMap($widgetMap);
    }

}
