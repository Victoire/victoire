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
        $parent = null;
        if ($widgetReference) {
            $parent = $this->em->getRepository('VictoireWidgetMapBundle:WidgetMap')->find($widgetReference);
        }
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

        $children = $widgetMap->getChildren($view);
        $beforeChild = !empty($children[WidgetMap::POSITION_BEFORE]) ? $children[WidgetMap::POSITION_BEFORE] : null;
        $afterChild = !empty($children[WidgetMap::POSITION_AFTER]) ? $children[WidgetMap::POSITION_AFTER] : null;

        $widgetMapReferenceChildren = $this->getChildrenByView($widgetMapReference);

        $widgetMap = $this->moveWidgetMap($view, $widgetMap, $widgetMapReference, $position, $slot);

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

        foreach ($widgetMapReferenceChildren['views'] as $_view) {
            if ($_view->getId() !== $view->getId()) {
            if (isset($widgetMapReferenceChildren['before'][$_view->getId()])) {
                $widgetMapReferenceChildren['before'][$_view->getId()]->setParent($widgetMap);
            }
            if (isset($widgetMapReferenceChildren['after'][$_view->getId()])) {
                $widgetMapReferenceChildren['after'][$_view->getId()]->setParent($widgetMap);
            }
        }
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
        $this->builder->build($view);

        $widgetMap = $view->getWidgetMapByWidget($widget);
        $slot = $widgetMap->getSlot();

        $originalParent = $widgetMap->getParent();
        $originalPosition = $widgetMap->getPosition();

        $children = $widgetMap->getChildren($view);
        $beforeChild = !empty($children[WidgetMap::POSITION_BEFORE]) ? $children[WidgetMap::POSITION_BEFORE] : null;
        $afterChild = !empty($children[WidgetMap::POSITION_AFTER]) ? $children[WidgetMap::POSITION_AFTER] : null;

        //we remove the widget from the current view
        if ($widgetMap->getView() === $view) {
            //remove the widget map from the slot
            $view->removeWidgetMap($widgetMap);
        } else {
            //the widget is owned by another view (a parent)
            //so we add a new widget map that indicates we delete this widget
            $replaceWidgetMap = new WidgetMap();
            $replaceWidgetMap->setAction(WidgetMap::ACTION_DELETE);
            $replaceWidgetMap->setWidget($widget);
            $replaceWidgetMap->setSlot($slot);
            $replaceWidgetMap->setReplaced($widgetMap);

            $view->addWidgetMap($replaceWidgetMap);
        }

        $this->moveChildren($view, $beforeChild, $afterChild, $originalParent, $originalPosition);
    }

    /**
     *
     * the widget is owned by another view (a parent)
     * so we add a new widget map that indicates we delete this widget
     *
     * @param View $view
     * @param WidgetMap $originalWidgetMap
     * @param Widget $widgetCopy
     * @throws \Exception
     */
    public function overwrite(View $view, WidgetMap $originalWidgetMap, Widget $widgetCopy)
    {
        $widgetMap = new WidgetMap();
        $widgetMap->setAction(WidgetMap::ACTION_OVERWRITE);
        $widgetMap->setReplaced($originalWidgetMap);
        $widgetMap->setWidget($widgetCopy);
        $widgetMap->setView($view);
        $widgetMap->setSlot($originalWidgetMap->getSlot());
        $widgetMap->setPosition($originalWidgetMap->getPosition());
        $widgetMap->setAsynchronous($widgetCopy->isAsynchronous());
        $widgetMap->setParent($originalWidgetMap->getParent());

        $view->addWidgetMap($widgetMap);
    }

    /**
     * If the moved widgetMap has someone at both his before and after, arbitrary move UP the before side
     * and find the first place after the before widgetMap hierarchy to place the after widgetMap.
     *
     * @param View $view
     * @param $beforeChild
     * @param $afterChild
     * @param $originalParent
     * @param $originalPosition
     */
    public function moveChildren(View $view, $beforeChild, $afterChild, $originalParent, $originalPosition)
    {
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

    protected function cloneWidgetMap(WidgetMap $widgetMap, View $view)
    {
            $originalWidgetMap = $widgetMap;
            $widgetMap = clone $widgetMap;
            $widgetMap->setId(null);
            $widgetMap->setAction(WidgetMap::ACTION_OVERWRITE);
            $widgetMap->setReplaced($originalWidgetMap);
        $originalWidgetMap->addSubstitute($widgetMap);
            $view->addWidgetMap($widgetMap);
            $this->em->persist($widgetMap);

        return $widgetMap;
    }
    protected function moveWidgetMap(View $view, WidgetMap $widgetMap, $parent = false, $position = false, $slot = false)
    {
        if ($widgetMap->getView() !== $view) {
            $widgetMap = $this->cloneWidgetMap($widgetMap, $view);
        }

        if ($parent !== false) {
            if ($oldParent = $widgetMap->getParent()) {
                $oldParent->removeChild($widgetMap);
            }
            $widgetMap->setParent($parent);
            if ($parent) {
                $parent->addChild($widgetMap);
            }
        }
        if ($position !== false) {
            $widgetMap->setPosition($position);
        }
        if ($slot !== false) {
            $widgetMap->setSlot($slot);
        }


        return $widgetMap;
    }

    public function getChildrenByView(WidgetMap $widgetMap)
    {
        $beforeChilds = $widgetMap->getChilds(WidgetMap::POSITION_BEFORE);
        $afterChilds = $widgetMap->getChilds(WidgetMap::POSITION_AFTER);

        $childrenByView['views'] = [];
        $childrenByView['before'] = [];
        $childrenByView['after'] = [];
        foreach ($beforeChilds as $beforeChild) {
            $view = $beforeChild->getView();
            $childrenByView['views'][] = $view;
            $childrenByView['before'][$view->getId()] = $beforeChild;
        }
        foreach ($afterChilds as $afterChild) {
            $view = $afterChild->getView();
            $childrenByView['views'][] = $view;
            $childrenByView['after'][$view->getId()] = $afterChild;
        }

        return $childrenByView;
    }
}
