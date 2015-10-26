<?php

namespace Victoire\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Entity\View;

class PageRenderEvent extends Event
{
    private $currentView;

    /**
     * Constructor.
     *
     * @param View $currentView
     */
    public function __construct(View $currentView)
    {
        $this->currentView = $currentView;
    }

    /**
     * @return View
     */
    public function getCurrentView()
    {
        return $this->currentView;
    }

    /**
     * @param View $currentView
     */
    public function setCurrentView($currentView)
    {
        $this->currentView = $currentView;
    }

}
