<?php
namespace Victoire\Bundle\PageBundle\Entity;

/**
 *
 */
class Slot
{
    //the id
    protected $id = null;
    protected $widgetMaps = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->widgetMaps = array();
    }

    /**
     * Get the id
     *
     * @return string The id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set the widget maps for this slot
     *
     * @param array $widgetMaps
     */
    public function setWidgetMaps($widgetMaps)
    {
        $this->widgetMaps = $widgetMaps;
    }

    /**
     * Get the widget maps
     *
     * @return array The widget maps
     */
    public function getWidgetMaps()
    {
        $this->reorderWidgetMaps();

        return $this->widgetMaps;
    }

    /**
     * Add a widget map to the list of widget maps
     *
     * @param WidgetMap $widgetMap
     */
    public function addWidgetMap(WidgetMap $widgetMap)
    {
        //Shift up others widgetsMaps's position
        foreach ($this->widgetMaps as $key => $_widgetMap) {
            if ($_widgetMap->getPosition() >= $widgetMap->getPosition()) {
                $_widgetMap->setPosition($_widgetMap->getPosition() + 1);
            }
        }
        $this->widgetMaps[] = $widgetMap;
    }

    /**
     * Get the widget map by the widget id
     *
     * @param integer $widgetId
     *
     * @return WidgetMap
     */
    public function getWidgetMapByWidgetId($widgetId)
    {
        $widgetMap = null;

        $widgetMaps = $this->widgetMaps;

        //parse the widgets maps
        foreach ($widgetMaps as $wm) {
            if ($wm->getWidgetId() === $widgetId) {
                $widgetMap = $wm;
                //entity found, there is no need to continue
                break;
            }
        }

        return $widgetMap;
    }

    /**
     * Remove the widget map from the slot
     *
     * @param WidgetMap $widgetMap
     */
    public function removeWidgetMap(WidgetMap $widgetMap)
    {
        $widgetMaps = $this->widgetMaps;

        //parse the widgets maps
        foreach ($widgetMaps as $index => $wm) {
            if ($wm->getWidgetId() === $widgetMap->getWidgetId()) {
                unset($this->widgetMaps[$index]);
                //entity found, there is no need to continue
                break;
            }
        }
        $this->reorderWidgetMaps();
    }

    /**
     * Redefine the widgetMap order (position)
     */
    public function reorderWidgetMaps()
    {
        $newWidgetMapsOrder = array(); //manipulation var
        $widgetMaps = array();         //Final widget Map var (reordered)
        //check and set the correct order
        foreach ($this->widgetMaps as $_widgetMap) {
            $newWidgetMapsOrder[$_widgetMap->getPosition()] = $_widgetMap;
        }

        //assign a following number for position 1, 2, 3, not 1, 3, 10
        ksort($newWidgetMapsOrder);
        foreach (array_values($newWidgetMapsOrder) as $key => $_widgetMap) {
            $_widgetMap->setPosition($key + 1); //+1 because position start to 1, not 0
            $widgetMaps[] = $_widgetMap;
        }
        $this->setWidgetMaps($widgetMaps);
    }
}
