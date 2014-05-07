<?php

namespace Victoire\Bundle\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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

        return array();
    }
}
