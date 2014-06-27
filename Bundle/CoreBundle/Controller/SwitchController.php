<?php

namespace Victoire\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Switch controller
 *
 * @Route("/victoire-dcms/switch")
 */
class SwitchController extends Controller
{
    /**
     * @Route("/{mode}", name="victoire_core_switch", options={"expose"=true})
     *
     * @param string  $mode    The mode
     *
     * @return JsonResponse Empty response
     */
    public function switchAction($mode)
    {
        //the session
        $session = $this->get('session');

        //memorize that we are in edit mode
        $session->set('victoire.edit_mode', $mode);

        return new JsonResponse();
    }
}
