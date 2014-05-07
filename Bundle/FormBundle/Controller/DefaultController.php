<?php

namespace Victoire\Bundle\FormBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('VictoireFormBundle:Default:index.html.twig', array('name' => $name));
    }
}
