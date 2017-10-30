<?php

namespace Victoire\Bundle\RedirectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class TestController
 *
 * @Route("/redirection")
 */
class RedirectionController extends Controller
{
    /**
     * @Route("/index", name="victoire_redirection_index")
     *
     * @param Request $request
     */
    public function indexAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $redirections = $em->getRepository('VictoireRedirectionBundle:Redirection')->findAll();

        return new JsonResponse(
            [
                'html' => $this->container->get('templating')->render(
                    $this->getBaseTemplatePath().':index.html.twig', [
                        'redirections' => $redirections
                    ]
                ),
            ]
        );
    }

    /**
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return 'VictoireRedirectionBundle:Redirection';
    }
}