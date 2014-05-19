<?php

namespace Victoire\Bundle\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * @author Thomas Beaujean thomas@appventus.com
 *
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

        return array();
    }
}
