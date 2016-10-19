<?php

namespace Victoire\Bundle\UIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class StyleguideController extends Controller
{
    /**
     * @Route("/styleguide")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
