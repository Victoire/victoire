<?php

namespace Victoire\Bundle\CoreBundle\Helper;

use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * This Helper give you the current view (BasePage or Template)
 * ~ victoire_core.current_view.
 */
class CurrentViewHelper
{
    protected $currentView;
    protected $updatedCurrentView;

    /**
     * Get currentView.
     *
     * @return View
     */
    public function getCurrentView()
    {
        return $this->currentView;
    }

    /**
     * Get updatedCurrentView.
     *
     * @return View
     */
    public function getUpdatedCurrentView()
    {
        return $this->updatedCurrentView;
    }

    /**
     * Set currentView.
     *
     * @param View $currentView
     *
     * @return $this
     */
    public function setCurrentView(View $currentView)
    {
        if ($this->currentView == null) {
            $this->currentView = clone $currentView;
        }
        $this->updatedCurrentView = $currentView;

        return $this;
    }

    /**
     * This method allow you to get the current view using a Current View Helper as a method
     * ex.
     * $currentViewHelper = $this->get('victoire_core.current_view');
     * $currentView = $currentViewHelper();.
     *
     * @return View The current View
     */
    public function __invoke()
    {
        if ($this->currentView) {
            return $this->currentView;
        }
    }
}
