<?php

namespace Victoire\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Switch controller
 *
 * @Route("/victoire-dcms/ui")
 */
class UIController extends Controller
{
    /**
     * Confirm modal
     * @param Request $request An HTTP request.
     *
     * @return array
     *
     * @Route("/confirm", name="victoire_core_ui_confirm", options={"expose"=true})
     * @Template
     */
    public function confirmAction(Request $request)
    {
        $confirmCallback = $request->get('confirm_callback');
        if ($confirmCallback === '') {
            $confirmCallback = null;
        }

        return array(
            'title'                => $request->get('title'),
            'body'                 => $request->get('body'),
            'id'                   => $request->get('id').'-'.uniqid().'-modal',
            'cancel_button_class'  => $request->get('cancel_button_class', 'vic-btn-cancel'),
            'confirm_button_class' => $request->get('confirm_button_class', 'vic-btn-danger'),
            'type'                 => $request->get('type'),
            'confirmCallback'      => $confirmCallback,
        );
    }
}
