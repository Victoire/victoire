<?php

namespace Victoire\Bundle\ViewReferenceBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;

class ViewReferenceEvent extends Event
{
    private $view;

    /**
     * ViewReferenceEvent constructor.
     * @param WebViewInterface $view
     */
    public function __construct(WebViewInterface $view)
    {
        $this->view = $view;
    }

    /**
     * @return WebViewInterface
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param WebViewInterface $view
     */
    public function setView(WebViewInterface $view)
    {
        $this->view = $view;
        return $this;
    }
}