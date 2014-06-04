<?php
namespace Victoire\Bundle\PageBundle\WidgetMap;

use Victoire\Bundle\PageBundle\Entity\BasePage as Page;
use Victoire\Bundle\CoreBundle\Entity\Widget;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Doctrine\ORM\EntityManager;

/**
 * Page WidgetMap builder
 *
 * ref: page.widgetMap.builder
 */
class WidgetMapBuilder
{
    protected $em = null;

    /**
     * Constructor
     *
     * @param EntityManager $em The entity manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Build page widget map builder
     * @param  Page   $page The page we want to build the widget map
     * @return array       The widget map as an array
     */
    public function build(Page $page)
    {
        $widgetMap = array();
        //Get all page/Template widgets
        foreach ($page->getWidgets() as $key => $widget) {
            if (!isset($widgetMap[$widget->getSlot()])) {
                $widgetMap[$widget->getSlot()] = array();
            }
            $widgetMap[$widget->getSlot()][] = $widget->getId();
        }

        //Then use old widgetMap to order them
        $oldWidgetMap = $page->getWidgetMap();

        $sortedWidgetMap = array();
        foreach ($oldWidgetMap as $slot => $widgets) {
            foreach ($widgets as $position => $widgetId) {
                if (isset($widgetMap[$slot]) && in_array($widgetId, $widgetMap[$slot])) {
                    if (!isset($sortedWidgetMap[$slot])) {
                        $sortedWidgetMap[$slot] = array();
                    }
                    $sortedWidgetMap[$slot][] = $widgetId;
                    unset($widgetMap[$slot][array_search($widgetId, $widgetMap[$slot])]);
                }
            }
        }
        foreach ($widgetMap as $slot => $widgets) {
            foreach ($widgets as $id) {
                if (!isset($sortedWidgetMap[$slot])) {
                    $sortedWidgetMap[$slot] = array();
                }
                $sortedWidgetMap[$slot][] = $id;
            }
        }

        if ($template = $page->getTemplate()) {
            $sortedWidgetMap = array_merge($sortedWidgetMap, $this->build($template));
        }

        return $sortedWidgetMap;
    }

    /**
     * Compute the complete widget map for a page by its parents
     *
     * @param Page   $page The page
     * @param string $slot The slot to get
     *
     * @return array The computed widgetMap
     *
     * @throws \Exception
     */
    public function computeCompleteWidgetMap(Page $page, $slotId)
    {
        $widgetMap = array();
        $parentWidgetMaps = null;
        $pageWidgetMaps = null;

        //get the parent widget map
        $parent = $page->getParent();

        if ($parent !== null) {
            $parentWidgetMaps = $this->computeCompleteWidgetMap($parent, $slotId);
        }

        $slot = $page->getSlotById($slotId);

        if ($slot !== null) {
            $pageWidgetMaps = $slot->getWidgetMaps();
        }

        if ($parentWidgetMaps !== null) {
            $widgetMap = array_merge($widgetMap, $parentWidgetMaps);
        }

        //if the current page have some widget maps
        if ($pageWidgetMaps !== null) {
            //we parse the widget maps
            foreach ($pageWidgetMaps as $pageWidgetMap) {
                //depending on the action
                $action = $pageWidgetMap->getAction();

                switch ($action) {
                    case WidgetMap::ACTION_CREATE:
                        $widgetMap[] = $pageWidgetMap;
                        break;
                    case WidgetMap::ACTION_REPLACE:
                        //parse the widget maps
                        foreach ($widgetMap as $index => $wm) {
                            if ($wm->getId() === $pageWidgetMap->getReplacedWidgetId()) {
                                //replace the widget map from the list
                                $widgetMap[$index] = $pageWidgetMap;
                            }
                        }
                        break;
                    case WidgetMap::ACTION_DELETE:
                        //parse the widget maps
                        foreach ($widgetMap as $index => $wm) {
                            if ($wm->getId() === $pageWidgetMap->getId()) {
                                //remove the widget map from the list
                                unset($widgetMap[$index]);
                            }
                        }
                        break;
                    default:
                        throw new \Exception('The action ['.$action.'] is not handeld yet.');
                        break;
                }
            }
        }

        return $widgetMap;
    }

    /**
     * Get the slots for the page by the sorted slots given by the reorder on the Screen
     *
     * @param Page $page
     * @param array $widgetSlots
     */
    public function getUpdatedSlotsByPage(Page $page, $widgetSlots)
    {
        throw new \Exception('The action updateOrder is not handeld yet.');
    }

    /**
     * Delete the widget from the page
     *
     * @param Page   $page
     * @param Widget $widget
     *
     * @throws \Exception The slot does not exists
     */
    public function deleteWidgetFromPage(Page $page, Widget $widget)
    {
        //the widget page
        $widgetPage = $widget->getPage();

        //the widget slot
        $widgetSlotId = $widget->getSlot();

        //the widget id
        $widgetId = $widget->getId();

        //get the slot
        $slot = $page->getSlotById($widgetSlotId);

        //we remove the widget from the current page
        if ($widgetPage === $page) {
            //test that the slot for the widget exists
            if ($slot === null) {
                throw new \Exception('The slot['.$widgetSlotId.'] for the widget ['.$widgetId.'] of the page ['.$page->getId().'] was not found.');
            }

            //get the widget map
            $widgetMap = $slot->getWidgetMapByWidgetId($widgetId);
            //remove the widget map from the slot
            $slot->removeWidgetMap($widgetMap);
        } else {
            //there might be no slot yet for the child page
            if ($slot === null) {
                //create a new slot
                $slot = new Slot();
                $slot->setId($widgetSlotId);

                //add the new slot to the page
                $page->addSlot($slot);
            }

            //the widget is owned by another page (a parent)
            //so we add a new widget map that indicates we delete this widget
            $widgetMap = new WidgetMap();
            $widgetMap->setAction(WidgetMap::ACTION_DELETE);
            $widgetMap->setWidgetId($widgetId);

            $slot->addWidgetMap($widgetMap);
        }
    }

    /**
     * Edit the widget from the page, if the widget is not linked to the current page, a copy is created
     *
     * @param Page   $page
     * @param Widget $widget
     *
     * @throws \Exception The slot does not exists
     */
    public function editWidgetFromPage(Page $page, Widget $widget)
    {
        //the widget page
        $widgetPage = $widget->getPage();

        //we only copy the widget if the page of the widget is not the current page
        if ($widgetPage !== $page) {
            //services
            $em = $this->em;

            $widgetCopy = clone $widget;
            $widgetCopy->setPage($page);

            //we have to persist the widget to get its id
            $em->persist($widgetCopy);
            $em->flush();

            //the id of the new widget
            $widgetId = $widgetCopy->getId();

            //the widget slot
            $widgetSlotId = $widget->getSlot();

            //the widget id
            $replacedWidgetId = $widget->getId();

            //get the slot
            $slot = $page->getSlotById($widgetSlotId);

            //there might be no slot yet for the child page
            if ($slot === null) {
                //create a new slot
                $slot = new Slot();
                $slot->setId($widgetSlotId);

                //add the new slot to the page
                $page->addSlot($slot);
            }

            //the widget is owned by another page (a parent)
            //so we add a new widget map that indicates we delete this widget
            $widgetMap = new WidgetMap();
            $widgetMap->setAction(WidgetMap::ACTION_REPLACE);
            $widgetMap->setReplacedWidgetId($replacedWidgetId);
            $widgetMap->setWidgetId($widgetId);

            $slot->addWidgetMap($widgetMap);

            $widget = $widgetCopy;
        }

        return $widget;
    }
}
