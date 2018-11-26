<?php

namespace Victoire\Bundle\UIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class StyleguideController extends Controller
{
    /**
     * @Route("/styleguide", name="victoire_ui_styleguide")
     * @Route("/styleguide/{component}", name="victoire_ui_styleguide")
     *
     * @return Response
     */
    public function indexAction($component = null)
    {
        $components = [
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
        ];

        return $this->render('@VictoireUI/Styleguide/index.html.twig', [
            'components' => $component ? [$component] : $components,
            'focus'      => $component != null,
        ]);
    }
}
