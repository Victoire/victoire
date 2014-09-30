<?php
namespace Victoire\Bundle\PageBundle\WidgetMap;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * View WidgetMap builder
 *
 * ref: view.widgetMap.builder
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
     * Remove the missing widgets from the widget map of a view
     *
     * @param View $view
     */
    public function removeMissingWidgets(View $view)
    {
        //get the slots of the view
        $slots = $view->getSlots();

        //the entity manager
        $em = $this->em;
        //the repository
        $widgetRepo = $em->getRepository('VictoireWidgetBundle:Widget');

        //parse the slots
        foreach ($slots as $slot) {
            $widgetMaps = $slot->getWidgetMaps();

            //parse the widget maps
            foreach ($widgetMaps as $widgetMap) {
                $widgetId = $widgetMap->getWidgetId();

                //get the widget by its id
                $widget = $widgetRepo->findOneById($widgetId);

                //if the widget is missing
                if ($widget === null) {
                    $missingWidget = true;
                } else {
                    $missingWidget = false;
                }

                //if the widget is missing
                if ($missingWidget) {
                    $slot->removeWidgetMap($widgetMap);
                }
            }
        }

        //we update the serialized widget map
        $view->updateWidgetMapBySlots();
    }

    /**
     * remove the widget ids from the widget map if any of the parents has it in its widget map
     * @param View $view
     */
    public function removeDuplicateWidgetLegacy(View $view)
    {
        //get the template of the view
        $template = $view->getTemplate();

        //if there is one
        if ($template !== null) {
            //get the list of ids of the parent
            $parentIds = $this->getCompleteWidgetIds($template);

            //get the slots of the view
            $slots = $view->getSlots();

            //parse the slots
            foreach ($slots as $slot) {
                $widgetMaps = $slot->getWidgetMaps();

                //parse the widget maps
                foreach ($widgetMaps as $widgetMap) {
                    //id of the widget
                    $widgetId = $widgetMap->getWidgetId();

                    //if the widget is in the parents
                    if (in_array($widgetId, $parentIds)) {
                        //we remove it from the widget map
                        $slot->removeWidgetMap($widgetMap);
                    }
                }
            }

            //we update the serialized widget map
            $view->updateWidgetMapBySlots();
        }
    }

    /**
     * Get the complete list of widget ids of the view and its parents
     *
     * @param View $view
     *
     * @return array The list of ids
     */
    protected function getCompleteWidgetIds(View $view)
    {
        $ids = array();

        $slots = $view->getSlots();

        //the entity manager
        $em = $this->em;
        //the repository
        $widgetRepo = $em->getRepository('VictoireWidgetBundle:Widget');

        //parse the slots
        foreach ($slots as $slot) {
            $widgetMaps = $slot->getWidgetMaps();

            //parse the widget maps
            foreach ($widgetMaps as $widgetMap) {
                $widgetId = $widgetMap->getWidgetId();

                //add the id to the list
                $ids[] = $widgetId;
            }
        }

        $template = $view->getTemplate();

        //if there is one
        if ($template !== null) {
            $parentIds = $this->getCompleteWidgetIds($template);

            //merge the ids
            $ids = array_merge($ids, $parentIds);
        }

        //remove duplicate entries
        $ids = array_unique($ids);

        return $ids;
    }

    /**
     * Compute the complete widget map for a view by its parents
     * @param View   $view   The view
     * @param string $slotId The slot to get
     *
     * @return array The computed widgetMap
     *
     * @throws \Exception
     */
    public function computeCompleteWidgetMap(View $view, $slotId)
    {
        $widgetMap = array();
        $parentWidgetMaps = null;
        $viewWidgetMaps = null;

        //get the template widget map
        $template = $view->getTemplate();

        if ($template !== null) {
            $parentWidgetMaps = $this->computeCompleteWidgetMap($template, $slotId);
        }

        $slot = $view->getSlotById($slotId);

        if ($slot !== null) {
            $viewWidgetMaps = $slot->getWidgetMaps();
        }

        //this array gives the position of the widget maps by its id
        $widgetMapPositionIndex = array();

        if ($parentWidgetMaps !== null) {
            //the parent widget map array might not have a clean index
            $index = 1;
            foreach ($parentWidgetMaps as $parentWidgetMap) {
                //id of the widget map
                $id = $parentWidgetMap->getWidgetId();
                //save the position of the widget map
                //the widget maps of the parent are each 100 units
                //so we can insert 99 widget map of the child between each widget map of the parent
                $widgetMapPosition = ($index * 100);
                $widgetMapPositionIndex[$id] = $widgetMapPosition;

                $widgetMap[$widgetMapPosition] = $parentWidgetMap;

                $index++;
            }

            unset($index);
        }

        //if the current view have some widget maps
        if ($viewWidgetMaps !== null) {
            //we parse the widget maps
            foreach ($viewWidgetMaps as $viewWidgetMap) {
                //depending on the action
                $action = $viewWidgetMap->getAction();

                switch ($action) {
                    case WidgetMap::ACTION_CREATE:
                        $position = $viewWidgetMap->getPosition();
                        $reference = $viewWidgetMap->getPositionReference();

                        //the 0 reference means the top of the view
                        if ($reference === 0) {
                            $parentPosition = 0;
                        } else {
                            //otherwise we look for the position of the widget map parent with this id
                            if (isset($widgetMapPositionIndex[$reference])) {
                                $parentPosition = $widgetMapPositionIndex[$reference];
                            } else {
                                //the widget of the parent has been deleted
                                //the widget comes at the top of the view
                                $parentPosition = 0;
                            }
                        }

                        //the position of the widget is the sum of the widget map position and the position of the widget map
                        $position += $parentPosition;

                        $position = $this->getNextAvailaiblePosition($position, $widgetMap);

                        $widgetMap[$position] = $viewWidgetMap;
                        break;
                    case WidgetMap::ACTION_REPLACE:
                        //parse the widget maps
                        foreach ($widgetMap as $index => $wm) {
                            if ($wm->getWidgetId() === $viewWidgetMap->getReplacedWidgetId()) {
                                //replace the widget map from the list
                                $widgetMap[$index] = $viewWidgetMap;
                            }
                        }
                        break;
                    case WidgetMap::ACTION_DELETE:
                        //parse the widget maps
                        foreach ($widgetMap as $index => $wm) {
                            if ($wm->getWidgetId() === $viewWidgetMap->getWidgetId()) {
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

        //the widget maps must be reordered by the indexes
        ksort($widgetMap, SORT_NUMERIC);

        return $widgetMap;
    }

    /**
     * Get the slots for the view by the sorted slots given by the Screen
     *
     * @param View  $view
     * @param array $widgetSlots
     */
    public function updateWidgetMapsByView(View $view, $widgetSlots)
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
     * Edit the widget from the view, if the widget is not linked to the current view, a copy is created
     *
     * @param View   $view
     * @param Widget $widget
     *
     * @return Widget The widget
     *
     * @throws \Exception The slot does not exists
     */
    public function editWidgetFromView(View $view, Widget $widget)
    {
        //the widget view
        $widgetView = $widget->getView();

        //we only copy the widget if the view of the widget is not the current view
        if ($widgetView !== $view) {

            $widgetCopy = clone $widget;
            $widgetCopy->setView($view);

            //Look for on_to_many relations, if found, duplicate related entities.
            //It is necessary for 'list' widgets, this algo duplicates and persists list items.
            $associations = $this->em->getClassMetadata(get_class($widget))->getAssociationMappings();
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($associations as $name => $values) {
                if ($values['type'] === ClassMetadataInfo::ONE_TO_MANY) {
                    $relatedEntities = $accessor->getValue($widget, $values['fieldName']);
                    $relatedEntitiesCopies = array();
                    foreach ($relatedEntities as $relatedEntity) {
                        $relatedEntityCopy = clone $relatedEntity;
                        $this->em->persist($relatedEntity);
                        $relatedEntitiesCopies[] = $relatedEntityCopy;
                    }
                    $accessor->setValue($widgetCopy, $name, $relatedEntitiesCopies);
                }
            }

            //we have to persist the widget to get its id
            $this->em->persist($view);
            $this->em->persist($widgetCopy);
            $this->em->flush();

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
            $widgetMap->setAction(WidgetMap::ACTION_REPLACE);
            $widgetMap->setReplacedWidgetId($replacedWidgetId);
            $widgetMap->setWidgetId($widgetId);

            $slot->addWidgetMap($widgetMap);

            $widget = $widgetCopy;
        }

        return $widget;
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

        $this->updateWidgetMapsByView($view, $widgetSlots);
        $view->updateWidgetMapBySlots();

        //update the view with the new widget map
        $this->em->persist($view);
        $this->em->flush();
    }
}
