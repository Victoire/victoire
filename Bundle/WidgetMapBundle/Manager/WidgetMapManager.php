<?php

namespace Victoire\Bundle\WidgetMapBundle\Manager;

use Doctrine\Orm\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetBundle\Model\Widget;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;
use Victoire\Bundle\WidgetMapBundle\Helper\WidgetMapHelper;

class WidgetMapManager
{
    private $em;
    private $builder;
    private $helper;

    public function __construct(EntityManager $em, WidgetMapBuilder $builder, WidgetMapHelper $helper)
    {
        $this->em = $em;
        $this->builder = $builder;
        $this->helper = $helper;
    }

    /**
     * compute the widget map for view.
     *
     * @param View  $view
     * @param array $sortedWidget
     *
     * @throws Exception
     */
    public function updateWidgetMapOrder(View $view, $sortedWidget)
    {
        $this->updateWidgetMapsFromView($view, $sortedWidget);
        $view->updateWidgetMapBySlots();

        //update the view with the new widget map
        $this->em->persist($view);
        $this->em->flush();
    }

    /**
     * Delete the widget from the view.
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
     * Get the slots for the view by the sorted slots given by the sortable js script when ordering widgets.
     *
     * @param View $view
     */
    protected function updateWidgetMapsFromView(View $view, $sortedWidget)
    {
        $parentWidgetId = (int) $sortedWidget['parentWidget']; //2
        $slotId = $sortedWidget['slot']; //content
        $widgetId = (int) $sortedWidget['widget']; //1
        $slot = $view->getSlotById($slotId);
        $originalWidgetMap = $slot->getWidgetMapByWidgetId($widgetId);

        // Get the moved widget in the current page or in template
        $watchdog = 100;
        $_view = $view;
        while (!$originalWidgetMap) {
            $watchdog--;
            $_view = $_view->getTemplate();
            $parentSlot = $_view->getSlotById($slotId);
            $originalWidgetMap = $parentSlot->getWidgetMapByWidgetId($widgetId);
            if (0 === $watchdog) {
                throw new \Exception(sprintf("The slot or the widget %s doesn't appears to be in any WidgetMap. You should check this manually.", $slotId, $widgetId));
            }
        }

        // If parentWidgetId is null, the widget was placed on first position
        if (null === $parentWidgetId) {
            $widgetMapEntry = new WidgetMap();
            $widgetMapEntry->setPosition(1);
            $widgetMapEntry->setAction($originalWidgetMap->getAction());
            $widgetMapEntry->setWidgetId($widgetId);
            $widgetMapEntry->setPositionReference(0);

        // If the parent of the sorted widget is from the current page
        } elseif ($parentWidgetMapEntry = $slot->getWidgetMapByWidgetId($parentWidgetId)) {
            // Place the widget just under the parent widget
            $widgetMapEntry = new WidgetMap();
            $widgetMapEntry->setPosition($parentWidgetMapEntry->getPosition() + 1);
            $widgetMapEntry->setAction($originalWidgetMap->getAction());
            $widgetMapEntry->setWidgetId($widgetId);
            $widgetMapEntry->setPositionReference($parentWidgetMapEntry->getPositionReference());

        // If the parent of the sorted widget is not from the current page
        } else {
            $widgetMapEntry = new WidgetMap();
            $widgetMapEntry->setPosition(1);
            $widgetMapEntry->setAction($originalWidgetMap->getAction());
            $widgetMapEntry->setPositionReference($parentWidgetId);
            $widgetMapEntry->setWidgetId($widgetId);
        }

        // If this WidgetMapEntry already in the page, remove it
        if ($oldWidgetMapEntry = $slot->getWidgetMapByWidgetId($widgetId)) {
            $widgetMapEntry->setAsynchronous($oldWidgetMapEntry->isAsynchronous());
            $slot->removeWidgetMap($oldWidgetMapEntry);
        // Else, the new widgetMap is an overwrite
        } elseif ($originalWidgetMap->getAction() !== WidgetMap::ACTION_OVERWRITE) {
            $widgetMapEntry->setAction(WidgetMap::ACTION_OVERWRITE);
            $widgetMapEntry->setReplacedWidgetId($widgetId);
        }
        // Insert the new one in page slot
        $this->helper->insertWidgetMapInSlot($slotId, $widgetMapEntry, $view);

        return;
    }

    /**
     * create a widgetMap for the new Widget cloned.
     *
     * @return void
     **/
    public function overwriteWidgetMap(Widget $widgetCopy, Widget $widget, View $view)
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

        $originalWidgetMap = $slot->getWidgetMapByWidgetId($replacedWidgetId);
        //If widgetmap was not found current view, we dig
        if (!$originalWidgetMap) {
            $watchDog = 100;
            $_view = $view;
            while (!$originalWidgetMap && $watchDog) {
                $_view = $_view->getTemplate();
                $parentSlot = $_view->getSlotById($widgetSlotId);
                if ($parentSlot) {
                    $originalWidgetMap = $parentSlot->getWidgetMapByWidgetId($replacedWidgetId);
                }
                $watchDog--;
            }

            if (0 == $watchDog) {
                throw new \Exception(sprintf("The slot %s doesn't appears to be in any templates WidgetMaps. You should check this manually.", $widgetSlotId));
            }
        }

        //the widget is owned by another view (a parent)
        //so we add a new widget map that indicates we delete this widget
        $widgetMap = new WidgetMap();
        $widgetMap->setAction(WidgetMap::ACTION_OVERWRITE);
        $widgetMap->setReplacedWidgetId($replacedWidgetId);
        $widgetMap->setWidgetId($widgetId);
        $widgetMap->setPosition($originalWidgetMap->getPosition());
        $widgetMap->setAsynchronous($widgetCopy->isAsynchronous());
        $widgetMap->setPositionReference($originalWidgetMap->getPositionReference());

        $slot->addWidgetMap($widgetMap);
    }
}
