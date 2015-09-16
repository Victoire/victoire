<?php

namespace Victoire\Bundle\TwigBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\TwigBundle\Entity\ErrorPage;

/**
 * Show an error page
 * @param Request $request The request
 * @param string  $code    The error page code
 *
 * @Route("/error", name="victoire_template_show")
 */
class ErrorPageController extends Controller
{
    /**
     * Show an error page
     *
     * @Route("/{code}", name="victoire_errorPage_show")
     * @ParamConverter("page", class="VictoireTwigBundle:ErrorPage")
     * @return Response
     */
    public function showAction(ErrorPage $page)
    {
        //add the view to twig
        $this->container->get('twig')->addGlobal('view', $page);
        $page->setReference(['id' => $page->getId()]);

        //the victoire templating
        $victoireTemplating = $this->container->get('victoire_templating');
        $layout = 'AppBundle:Layout:'.$page->getTemplate()->getLayout().'.html.twig';

        $parameters = array(
            'view'   => $page,
            'id'     => $page->getId(),
            'locale' => $page->getLocale(),
        );

        $this->get('victoire_widget_map.builder')->build($page);
        $this->container->get('victoire_core.current_view')->setCurrentView($page);

        //create the response
        $response = $victoireTemplating->renderResponse(
            $layout,
            $parameters
        );

        return $response;
    }
}
