<?php
namespace Victoire\Bundle\PageBundle\Entity;

use Victoire\Bundle\PageBundle\Entity\WidgetMap;

/**
 *
 * @author Thomas Beaujean
 *
 */
class Slot
{
    //the id
    protected $id = null;
    protected $widgetMaps = null;

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
        return $this->widgetMaps;
    }

    /**
     * Add a widget map to the list of widget maps
     *
     * @param WidgetMap $widgetMap
     */
    public function addWidgetMap(WidgetMap $widgetMap)
    {
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
                continue;
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
            if ($wm->getId() === $widgetMap->getId()) {
                unset($this->widgetMaps[$index]);
                //entity found, there is no need to continue
                continue;
            }
        }

    }
}