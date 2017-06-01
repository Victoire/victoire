<?php

namespace Victoire\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
     * @return array
     *
     * @Route("/confirm", name="victoire_core_ui_confirm", options={"expose"=true})
     * @Template
     */
    public function confirmAction(Request $request)
    {
        return [
            'id' => $request->get('id').'-'.uniqid().'-modal'
        ];
    }
}
