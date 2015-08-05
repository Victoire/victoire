<?php

namespace Victoire\Bundle\CoreBundle\Listener;

use \Symfony\Component\HttpKernel\HttpKernelInterface;
use \Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ControllerListener {

    /**
     * @param FilterControllerEvent $event
     */
    public function preExecuteAutorun(FilterControllerEvent $event) {
        // Event catching
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            // controller catching
            $_controller = $event->getController();
            if (isset($_controller[0])) {
                $controller = $_controller[0];
            }
            // preExecute method verification
            if(method_exists($controller,'preExecute')) {
                $controller->preExecute();
            }
        }
    }

}

