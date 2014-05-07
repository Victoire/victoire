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
 */
class SwitchController extends Controller
{

    /**
     * @Route("/switch/{mode}", name="victoire_cms_switch", options={"expose"=true})
     */
    public function switchAction(Request $request, $mode)
    {
        $this->get('session')->set('victoire.edit_mode', $mode);
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }
}
