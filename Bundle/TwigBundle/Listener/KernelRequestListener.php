<?php

namespace Victoire\Bundle\TwigBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * KernelRequestListener will do some things like adding config as globals (ie responsive vars).
 *
 * @author Leny BERNARD <leny@appventus.com>
 */
class KernelRequestListener
{
    protected $twig;

    public function __construct(\Twig_Environment $twig, $responsiveConfig)
    {
        $this->twig = $twig;
        $this->responsiveConfig = $responsiveConfig;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->twig->addGlobal('victoire_twig_responsive', $this->responsiveConfig);
    }
}
