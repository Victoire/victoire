<?php

namespace Victoire\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Switch controller
 *
 * @Route("/victoire-dcms/switch")
 */
class SwitchController extends Controller
{
    /**
     * Method used to change of edit mode
     * @param string $mode The mode
     *
     * @Route("/mode/{mode}", name="victoire_core_switchMode", options={"expose"=true})
     * @return JsonResponse Empty response
     */
    public function switchModeAction($mode)
    {
        //the session
        $session = $this->get('session');

        //memorize that we are in edit mode
        $session->set('victoire.edit_mode', $mode);

        return new JsonResponse(
            array('response' => true)
        );
    }
}
