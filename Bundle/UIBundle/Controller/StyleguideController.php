<?php

namespace Victoire\Bundle\UIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class StyleguideController extends Controller
{
    /**
     * @Route("/styleguide", name="victoire_ui_styleguide")
     * @Route("/styleguide/{component}", name="victoire_ui_styleguide")
     * @Template()
     */
    public function indexAction($component = null)
    {
        $components = array(
            // 'styleguide',
            'color',
            'button',
            'dropdown',
            // 'layout',
            // 'mode-switcher',
            // 'navbar',
            // 'modal',
            // 'form',
            // 'tooltip',
            // 'card',
            // 'list-group',
            // 'slot',
            // 'widget-overlay',
        );

        return array(
            'components' => $component ? array($component) : $components,
            'focus' => $component != null,
        );
    }
}
