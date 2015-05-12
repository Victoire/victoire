<?php

namespace Victoire\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\Event;

/**
 * Backend controller
 */
abstract class BackendController extends Controller
{
    /**
     * This method is run for each backend request by the victoire_core.controller.pre_execute_listener
     * Must be public to be run outside in ControllerListener
     * @return void
     */
    public function preExecute()
    {
        $this->get('event_dispatcher')->dispatch('victoire_core.backend_menu.global', new Event());
    }
}
