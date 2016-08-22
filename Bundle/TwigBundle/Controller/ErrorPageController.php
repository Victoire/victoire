<?php

namespace Victoire\Bundle\TwigBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\TwigBundle\Entity\ErrorPage;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * Show an error page.
 *
 * @param Request $request The request
 * @param string  $code    The error page code
 *
 * @Route("/victoire-dcms/error")
 */
class ErrorPageController extends Controller
{
    /**
     * Show an error page.
     *
     * @Route("/{code}", name="victoire_errorPage_show")
     *
     * @return Response
     */
    public function showAction(ErrorPage $page)
    {
        //add the view to twig
        $this->container->get('twig')->addGlobal('view', $page);
        $page->setReference(new ViewReference($page->getId()));
        $parameters = [
            'view'   => $page,
            'id'     => $page->getId(),
            'locale' => $page->getCurrentLocale(),
        ];

        $this->get('victoire_widget_map.builder')->build($page);
        $this->get('victoire_widget_map.widget_data_warmer')->warm(
            $this->get('doctrine.orm.entity_manager'),
            $page
        );

        $this->container->get('victoire_core.current_view')->setCurrentView($page);

        //create the response
        $response = $this->container->get('templating')->renderResponse(
            'VictoireCoreBundle:Layout:'.$page->getTemplate()->getLayout().'.html.twig',
            $parameters
        );

        return $response;
    }
}
