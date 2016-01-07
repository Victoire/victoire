<?php

namespace Victoire\Bundle\WidgetMapBundle\Manager;

use Doctrine\Orm\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;

class WidgetMapManager
{
    private $em;
    private $builder;

    public function __construct(EntityManager $em, WidgetMapBuilder $builder)
    {
        $this->em = $em;
        $this->builder = $builder;
    }


    public function insert(Widget $widget, View $view, $slotId, $position, $widgetReference)
    {

        $parent = $this->em->getRepository('VictoireWidgetMapBundle:WidgetMap')->findOneBy(['id' => $widgetReference]);
        //create the new widget map
        $widgetMapEntry = new WidgetMap();
        $widgetMapEntry->setAction(WidgetMap::ACTION_CREATE);
        $widgetMapEntry->setWidget($widget);
        $widgetMapEntry->setSlot($slotId);
        $widgetMapEntry->setPosition($position);
        $widgetMapEntry->setParent($parent);

        $view->addWidgetMap($widgetMapEntry);
    }

    /**
     * moves a widget in a view.
     *
     * @param View  $view
     * @param array $sortedWidget
     *
     * @returns void
     */
    public function move(View $view, $sortedWidget)
    {

        /** @var WidgetMap $widgetMapReference */
        $widgetMapReference = $this->em->getRepository('VictoireWidgetMapBundle:WidgetMap')->findOneById((int) $sortedWidget['widgetMapReference']);
        $position = $sortedWidget['position'];
        $slot = $sortedWidget['slot'];
        /** @var WidgetMap $widgetMap */
        $widgetMap = $this->em->getRepository('VictoireWidgetMapBundle:WidgetMap')->findOneById((int) $sortedWidget['widgetMap']);


        $originalParent = $widgetMap->getParent();
        $originalPosition = $widgetMap->getPosition();

        $this->moveWidgetMap($view, $widgetMap, $widgetMapReference, $position, $slot);

        $beforeChild = $widgetMap->getChild(WidgetMap::POSITION_BEFORE);
        $afterChild = $widgetMap->getChild(WidgetMap::POSITION_AFTER);
        if ($beforeChild) {
            $this->moveWidgetMap($view, $beforeChild, $originalParent, $originalPosition);

            $child = $beforeChild;
            while ($child->getChild(WidgetMap::POSITION_AFTER)) {
                $child = $child->getChild(WidgetMap::POSITION_AFTER);
            }
            $this->moveWidgetMap($view, $afterChild, $child);
        } else if ($afterChild) {
            $this->moveWidgetMap($view, $afterChild, $originalParent);
        }


//        $widgetMapReference = null;
//        if ($sortedWidget['widgetMapReference']) {
//            $widgetMapReference = $this->em->getRepository('VictoireWidgetMapBundle:WidgetMap')->findOneById((int) $sortedWidget['widgetMapReference']);
//        }
//        $widget = $this->em->getRepository('VictoireWidgetBundle:Widget')->findOneById((int) $sortedWidget['widget']);
//        $slot = $sortedWidget['slot']; //content
//        $originalWidgetMap = $view->getWidgetMapByWidget($widget);
//        $widgetMap = $this->builder->build($view);
//
//
//        $widgetMapEntry = new WidgetMap();
//        $widgetMapEntry->setAsynchronous($originalWidgetMap->isAsynchronous());
//        $widgetMapEntry->setSlot($slot);
//        $widgetMapEntry->setWidget($widget);
//        $widgetMapEntry->setAction($originalWidgetMap->getAction());
//        $widgetMapEntry = $this->generateWidgetPosition($widgetMapEntry, $widgetMap, $parentWidget->getId(), $view);
//
//        if ($widgetMapReference) {
//
//
//
//            // If the parent of the sorted widget is not from the current page
//        } else {
//            $widgetMapEntry->setPosition(1);
//            $widgetMapEntry->setAction($originalWidgetMap->getAction());
//            $widgetMapEntry->setPositionReference($parentWidget);
//        }
//        // If this WidgetMapEntry already in the page, remove it
//        if ($originalWidgetMap->getView() == $view) {
//            $view->removeWidgetMap($originalWidgetMap);
//            // Else, the new widgetMap is an overwrite
//        } elseif ($originalWidgetMap->getAction() !== WidgetMap::ACTION_OVERWRITE) {
//            $widgetMapEntry->setAction(WidgetMap::ACTION_OVERWRITE);
//            $widgetMapEntry->setReplacedWidget($widget);
//        }
//        // Insert the new one in page slot
//
//        $view->addWidgetMap($widgetMapEntry);

    }

    protected function moveWidgetMap(View $view, WidgetMap $widgetMap, $parent = false, $position = false, $slot = false)
    {
        if ($widgetMap->getView() !== $view) {
            $originalWidgetMap = $widgetMap;
            $widgetMap = clone $widgetMap;
            $widgetMap->setAction(WidgetMap::ACTION_OVERWRITE);
            $widgetMap->setReplaced($originalWidgetMap);
            $widgetMap->setView($view);
            $this->em->persist($widgetMap);
        }

        if ($parent !== false) {
            $widgetMap->setParent($parent);
        }
        if ($position !== false) {
            $widgetMap->setPosition($position);
        }
        if ($slot !== false) {
            $widgetMap->setSlot($slot);
        }

        return $widgetMap;
    }

    /**
     * Delete the widget from the view.
     *
     * @param View   $view
     * @param Widget $widget
     *
     * @throws \Exception Widget map does not exists
     */
    public function delete(View $view, Widget $widget)
    {

        //the widget id
        $widgetId = $widget->getId();

        $widgetMap = $view->getWidgetMapByWidget($widget);
        $slot = $widgetMap->getSlot();
        //we remove the widget from the current view
        if ($widgetMap->getView() === $view) {
            //remove the widget map from the slot
            $view->removeWidgetMap($widgetMap);
        } else {
            //the widget is owned by another view (a parent)
            //so we add a new widget map that indicates we delete this widget
            $widgetMap = new WidgetMap();
            $widgetMap->setAction(WidgetMap::ACTION_DELETE);
            $widgetMap->setWidget($widget);
            $widgetMap->setSlot($slot);

            $view->addWidgetMap($widgetMap);
        }
    }

}
