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
        $entityManager = $this->get('doctrine.orm.entity_manager');

        //get the page
        $templates = $entityManager->getRepository('VictoireTemplateBundle:Template')->findAll();
        $pages = $entityManager->getRepository('VictoirePageBundle:Page')->findAll();
        $homepage = $entityManager->getRepository('VictoirePageBundle:Page')->findOneByHomepage();

        return array(
            "templates" => $templates,
            "pages"     => $pages,
            "homepage"  => $homepage
        );
    }
}
