<?php

namespace Victoire\Bundle\TwigBundle\Extension;

class ResponsiveExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $responsive;

    public function __construct($responsive)
    {
        $this->responsive = $responsive;
    }

    public function getGlobals()
    {
        return [
            'victoire_twig_responsive' => $this->responsive,
        ];
    }
}
