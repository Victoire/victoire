<?php
namespace Victoire\Bundle\PageBundle\Helper;

class CurrentViewHelper
{
    protected $currentView;

    /**
     * Get currentView
     *
     * @return string
     */
    public function getCurrentView()
    {
        return $this->currentView;
    }

    /**
     * Set currentView
     *
     * @param string $currentView
     *
     * @return $this
     */
    public function setCurrentView($currentView)
    {
        $this->currentView = $currentView;

        return $this;
    }

    public function __invoke()
    {
        if ($this->currentView) {
            return $this->currentView;
        }
    }

}
