<?php

namespace Victoire\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Switch controller.
 *
 * @Route("/victoire-dcms/ui")
 */
class UIController extends Controller
{
    /**
     * Confirm modal.
     *
     * @param Request $request An HTTP request.
     *
     * @Route("/confirm", name="victoire_core_ui_confirm", options={"expose"=true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmAction(Request $request)
    {
        return $this->render('@VictoireCore/ui/confirm.html.twig', [
            'id' => $request->get('id').'-'.uniqid().'-modal',
        ]);
    }
}
