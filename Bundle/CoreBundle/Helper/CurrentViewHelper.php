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
    protected $mainCurrentView;

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
     * Get mainCurrentView.
     *
     * @return View
     */
    public function getMainCurrentView()
    {
        return $this->mainCurrentView;
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
            $this->mainCurrentView = clone $currentView;
        }
        $this->currentView = $currentView;

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
