<?php

namespace Victoire\Bundle\CoreBundle\Listener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Victoire\Bundle\CoreBundle\Controller\BackendController;
use Symfony\Component\EventDispatcher\Event;

class ControllerListener
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function preExecuteAutorun(FilterControllerEvent $event)
    {
        // Event catching
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            // controller catching
            $_controller = $event->getController();
            if (isset($_controller[0]) && $_controller[0] instanceof BackendController) {
                $this->eventDispatcher->dispatch('victoire_core.backend_menu.global', new Event());
            }
        }
    }
}
