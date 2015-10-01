<?php

namespace Victoire\Bundle\WidgetBundle\Model;

use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * Widget Model.
 */
abstract class Widget
{
    const MODE_ENTITY = 'entity';
    const MODE_QUERY = 'query';
    const MODE_STATIC = 'static';
    const MODE_BUSINESS_ENTITY = 'businessEntity';

    /**
     * The entity linked to the widget.
     *
     * @var unknown
     */
    protected $entity;

    /**
     * This property is not persisted, we use it to remember the view where the widget
     * is actually rendered.
     */
    protected $currentView;

    /**
     * @return string
     */
    public function isAsynchronous()
    {
        return $this->asynchronous;
    }

    /**
     * @param string $asynchronous
     */
    public function setAsynchronous($asynchronous)
    {
        $this->asynchronous = $asynchronous;
    }

    /**
     * Set the entity proxy.
     *
     * @param EntityProxy $entityProxy
     */
    public function setEntityProxy(EntityProxy $entityProxy)
    {
        $this->entityProxy = $entityProxy;
    }

    /**
     * Get the entity proxy.
     *
     * @return EntityProxy
     */
    public function getEntityProxy()
    {
        return $this->entityProxy;
    }

    /**
     * to string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fields.
     *
     * @param string $fields
     *
     * @return Widget
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Get fields.
     *
     * @return string
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set The Id.
     *
     * @param int $id The id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set slot.
     *
     * @param string $slot
     *
     * @return Widget
     */
    public function setSlot($slot)
    {
        $this->slot = $slot;

        return $this;
    }

    /**
     * Get slot.
     *
     * @return string
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * Set theme.
     *
     * @param string $theme
     *
     * @return Widget
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme.
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set view.
     *
     * @param string $view
     *
     * @return Widget
     */
    public function setView($view)
    {
        if ($view !== null) {
            $view->addWidget($this);
        }
        $this->view = $view;

        return $this;
    }

    /**
     * Get view.
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set the entity.
     *
     * @param unknown $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Get the entity.
     *
     * @return number
     */
    public function getEntity()
    {
        //if there is no entity
        if ($this->entity === null) {
            //we try to get one from the proxy
            $entityProxy = $this->getEntityProxy();

            //if there is a proxy
            if ($entityProxy !== null) {
                $entity = $entityProxy->getEntity($this->getBusinessEntityId());
                $this->entity = $entity;
            }
        }

        return $this->entity;
    }

    /**
     * Get the content.
     *
     * @return unknown
     */
    public function getValue()
    {
        //return $this->getContent();
        return;
    }

    /**
     * Set the current view.
     *
     * @param \Victoire\Bundle\CoreBundle\Entity\View $currentView
     *
     * @return \Victoire\Bundle\WidgetBundle\Entity\Widget
     */
    public function setCurrentView($currentView)
    {
        $this->currentView = $currentView;

        return $this;
    }

    /**
     * Get the current view.
     *
     * @return \Victoire\Bundle\CoreBundle\Entity\View The current view
     */
    public function getCurrentView()
    {
        return $this->currentView ? $this->currentView : $this->getView();
    }

    /**
     * Get the type of the object.
     *
     * @return string The type
     */
    public function getType()
    {
        return $this->guessType();
    }

    /**
     * Guess the type of this by exploding and getting the last item.
     *
     * @return string The guessed type
     */
    protected function guessType()
    {
        $type = explode('\\', get_class($this));

        return strtolower(preg_replace('/Widget/', '', end($type)));
    }

    /**
     * Set the mode.
     *
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Get the mode.
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get the view id.
     *
     * @return int The view id
     */
    public function getViewId()
    {
        $viewId = null;

        $view = $this->getView();

        if ($view !== null) {
            $viewId = $view->getId();
        }

        return $viewId;
    }

    /**
     * Clone a widget.
     */
    public function __clone()
    {
        // if there is a proxy
        if ($this->entityProxy) {
            // we clone this one
            $this->entityProxy = clone $this->entityProxy;
        }

        // This check should be in the __constructor, but Doctrine does not use __constructor to
        // instanciate entites but __clone method.
        if (property_exists(get_called_class(), 'widget')) {
            throw new \Exception(sprintf('A property $widget was found in %s object.
                The $widget property is reserved for Victoire.
                You should chose a different property name.', get_called_class()));
        }
    }
}
