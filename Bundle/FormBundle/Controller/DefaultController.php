<?php

namespace Victoire\Bundle\FormBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 *
 * @author Paul Andrieux
 *
 */
class DefaultController extends Controller
{
    /**
     * Index
     * @param unknown $name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($name)
    {
        return $this->render('VictoireFormBundle:Default:index.html.twig', array('name' => $name));
    }
}
