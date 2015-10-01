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
     * Constructor.
     */
    public function __construct()
    {
        $this->widgetMaps = [];
    }

    /**
     * Get the id.
     *
     * @return string The id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id.
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set the widget maps for this slot.
     *
     * @param array $widgetMaps
     */
    public function setWidgetMaps($widgetMaps)
    {
        $this->widgetMaps = $widgetMaps;
    }

    /**
     * Get the widget maps.
     *
     * @return WidgetMap[] The widget maps
     */
    public function getWidgetMaps()
    {
        return $this->widgetMaps;
    }

    /**
     * Add a widget map to the list of widget maps.
     *
     * @param WidgetMap $widgetMap
     */
    public function addWidgetMap(WidgetMap $widgetMap)
    {
        $this->widgetMaps[] = $widgetMap;
    }

    /**
     * Update the given widgetMap.
     *
     * @param WidgetMap $widgetMap
     *
     * @return this
     */
    public function updateWidgetMap($widgetMap)
    {
        //parse all widfgetMaps
        foreach ($this->widgetMaps as $key => $_widgetMap) {
            //if this the widgetMap we are looking for
            if ($_widgetMap->getWidgetId() === $widgetMap->getWidgetId()) {
                $this->widgetMaps[$key] = $widgetMap;
                //there no need to continue, we found the slot
                break;
            }
        }

        return $this;
    }

    /**
     * Get the widget map by the widget id.
     *
     * @param int $widgetId
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
     * Remove the widget map from the slot.
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
    }
}
