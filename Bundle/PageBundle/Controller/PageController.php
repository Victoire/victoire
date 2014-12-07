<?php
namespace Victoire\Bundle\PageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Victoire\Bundle\PageBundle\Entity\Page;
use Symfony\Component\HttpFoundation\Request;

/**
 * undocumented class
 *
 **/
class PageController extends BasePageController
{
    /**
     * Show homepage or redirect to new page
     *
     * ==========================
     * find homepage
     * if homepage
     *     forward show(homepage)
     * else
         *     redirect to welcome page (dashboard)
     * ==========================
     *
     * @Route("/", name="victoire_core_page_homepage")
     * @return template
     *
     */
    public function homepageAction(Request $request)
    {
        //services
        $entityManager = $this->getDoctrine()->getManager();

        //get the homepage
        $homepage = $entityManager->getRepository('VictoirePageBundle:BasePage')->findOneByHomepage(true);

        if ($homepage !== null) {
            return $this->showAction($request, $homepage->getUrl());
        } else {
            return $this->redirect($this->generateUrl('victoire_dashboard_default_welcome'));
        }
    }

}
