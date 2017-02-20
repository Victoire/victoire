<?php

namespace Victoire\Bundle\UIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StyleguideController extends Controller
{
    /**
     * @Route("/styleguide", name="victoire_ui_styleguide")
     * @Route("/styleguide/{component}", name="victoire_ui_styleguide")
     * @Template()
     */
    public function indexAction($component = null)
    {
        $components = [
            // 'styleguide',
            'color',
            'text',
            'heading',
            'layout',
            'button',
            'drops',
            'fab',
            'form',
            'slots',
            'widgets',
            'input-images',
            'list-group',
            'seo',
            'image',
            'menu',
            'mode-switcher',
            'navbar',
            'modal',
            'collapse',
            'alert',
            // 'tabs',
            // 'card',
        ];

        return [
            'components' => $component ? [$component] : $components,
            'focus'      => $component != null,
        ];
    }
}
