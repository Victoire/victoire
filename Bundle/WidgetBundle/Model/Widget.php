<?php

namespace Victoire\Bundle\WidgetBundle\Model;

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
     * This property is not persisted, we use it to remember the view where the widget
     * is actually rendered.
     */
    protected $currentView;

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
}
