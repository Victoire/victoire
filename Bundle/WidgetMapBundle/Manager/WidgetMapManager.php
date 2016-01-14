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

        $parent = $this->em->getRepository('VictoireWidgetMapBundle:WidgetMap')->find($widgetReference);
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
        $widgetMapReference = $this->em->getRepository('VictoireWidgetMapBundle:WidgetMap')->find((int) $sortedWidget['widgetMapReference']);
        $position = $sortedWidget['position'];
        $slot = $sortedWidget['slot'];
        /** @var WidgetMap $widgetMap */
        $widgetMap = $this->em->getRepository('VictoireWidgetMapBundle:WidgetMap')->find((int) $sortedWidget['widgetMap']);

        $originalParent = $widgetMap->getParent();
        $originalPosition = $widgetMap->getPosition();

        $children = $widgetMap->getChildren();
        $beforeChild = !empty($children[WidgetMap::POSITION_BEFORE]) ? $children[WidgetMap::POSITION_BEFORE] : null;
        $afterChild = !empty($children[WidgetMap::POSITION_AFTER]) ? $children[WidgetMap::POSITION_AFTER] : null;

        $widgetMap = $this->moveWidgetMap($view, $widgetMap, $widgetMapReference, $position, $slot);

        $widgetMap->removeChildren();

        // If the moved widgetMap has someone at both his before and after, arbitrary move UP the before side
        // and find the first place after the before widgetMap hierarchy to place the after widgetMap.
        if ($beforeChild && $afterChild) {
            $this->moveWidgetMap($view, $beforeChild, $originalParent, $originalPosition);

            $child = $beforeChild;
            while ($child->getChild(WidgetMap::POSITION_AFTER)) {
                $child = $child->getChild(WidgetMap::POSITION_AFTER);
            }
            if ($afterChild->getId() !== $child->getId()) {
                $this->moveWidgetMap($view, $afterChild, $child);
            }
        } else if ($beforeChild) {
            $this->moveWidgetMap($view, $beforeChild, $originalParent, $originalPosition);
        } else if ($afterChild) {
            $this->moveWidgetMap($view, $afterChild, $originalParent, $originalPosition);
        }

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

    protected function moveWidgetMap(View $view, WidgetMap $widgetMap, $parent = false, $position = false, $slot = false)
    {
        if ($widgetMap->getView() !== $view) {
            $originalWidgetMap = $widgetMap;
            $widgetMap = clone $widgetMap;
            $widgetMap->setAction(WidgetMap::ACTION_OVERWRITE);
            $widgetMap->setReplaced($originalWidgetMap);
            $view->addWidgetMap($widgetMap);
            $this->em->persist($widgetMap);
        }


        if ($parent !== false) {
            if ($originalParent = $widgetMap->getParent()) {
                $originalParent->removeChild($widgetMap);
            }
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
}
