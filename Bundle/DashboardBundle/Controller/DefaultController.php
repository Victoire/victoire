<?php

namespace Victoire\Bundle\DashboardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * The Victoire scaffolder Controller, when you're building the app
 * @Route("/victoire-dcms/dashboard")
 *
 */
class DefaultController extends Controller
{
    /**
     * Welcome page
     *
     * @return template
     * @Route("/welcome", name="victoire_dashboard_default_welcome")
     * @Template()
     */
    public function welcomeAction()
    {

        $em = $this->getEntityManager();

        //get the page
        $templates = $em->getRepository('VictoireTemplateBundle:Template')->findAll();
        $pages = $em->getRepository('VictoirePageBundle:Page')->findAll();
        $homepage = $em->getRepository('VictoirePageBundle:Page')->findOneByHomepage();

        return array(
            "templates" => $templates,
            "pages"     => $pages,
            "homepage"  => $homepage
        );
    }
}
