<?php

namespace Victoire\Bundle\ViewReferenceBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/victoire-dcms/view-reference")
 */
class ViewReferenceController extends Controller
{
    /**
     * @Route("/get", name="victoire_view_reference_get", options={"expose"=true})
     */
    public function getAllAction(Request $request)
    {
        return new JsonResponse(
            $this->get('victoire_view_reference.repository')->getChoices($request->getLocale())
        );
    }
}
