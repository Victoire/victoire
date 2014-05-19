<?php

namespace Victoire\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

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
     * @param Request $request The request
     * @param string  $mode    The mode
     *
     * @return Response The redirect
     */
    public function switchAction(Request $request, $mode)
    {
        $this->get('session')->set('victoire.edit_mode', $mode);
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }
}
