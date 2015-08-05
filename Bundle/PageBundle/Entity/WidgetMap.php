<?php

namespace Victoire\Bundle\PageBundle\Entity;

/**
 *
 */
class WidgetMap
{
    const ACTION_CREATE = 'create';
    const ACTION_OVERWRITE = 'overwrite';
    const ACTION_DELETE = 'delete';

    //the action
    protected $action = null;

    //the id of the widget
    protected $widgetId = null;

    //the id of the widget replaced (only in action replace)
    protected $replacedWidgetId = null;

    //the position of the widget
    protected $position = null;

    //does the widget has to be loaded asynchronously
    protected $asynchronous = null;

    //the position of the widget according to its template's widgets
    //the widget is after the widget positionned at this position
    //the position 0 is the top of the page
    protected $positionReference = null;

    /**
     * @return null
     */
    public function isAsynchronous()
    {
        return $this->asynchronous;
    }

    /**
     * @param null $asynchronous
     */
    public function setAsynchronous($asynchronous)
    {
        $this->asynchronous = $asynchronous;
    }

    /**
     * Set the action
     *
     * @param string $action
     *
     * @throws \Exception The action is not valid
     */
    public function setAction($action)
    {
        //test validity of the action
        if ($action !== self::ACTION_CREATE && $action !== self::ACTION_OVERWRITE && $action !== self::ACTION_DELETE) {
            throw new \Exception('The action of the widget map is not valid. Action: ['.$action.']');
        }

        $this->action = $action;
    }

    /**
     * Get the action
     *
     * @return string The action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the widget id
     *
     * @param string $widgetId
     */
    public function setWidgetId($widgetId)
    {
        $this->widgetId = $widgetId;
    }

    /**
     * Get the widget id
     *
     * @return string The widget id
     */
    public function getWidgetId()
    {
        return $this->widgetId;
    }

    /**
     * Set the position
     *
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get the position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get the replacedWidgetId
     *
     * @return integer the id of the replaced widget
     */
    public function getReplacedWidgetId()
    {
        return $this->replacedWidgetId;
    }

    /**
     * Set the replaced widget id
     *
     * @param integer $replacedWidgetId
     */
    public function setReplacedWidgetId($replacedWidgetId)
    {
        $this->replacedWidgetId = $replacedWidgetId;
    }

    /**
     * Set the position reference
     *
     * @param unknown $positionReference
     */
    public function setPositionReference($positionReference)
    {
        $this->positionReference = $positionReference;
    }

    /**
     * Get the positionReference
     *
     * @return integer The position reference
     */
    public function getPositionReference()
    {
        return $this->positionReference;
    }
}
