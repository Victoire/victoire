<?php

namespace Victoire\Bundle\I18nBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * KernelRequestListener add the available locas as global twig variable.
 *
 * @author Paul Andrieux <paul@victoire.io>
 */
class KernelRequestListener
{
    protected $twig;
    protected $availableLocales;

    public function __construct(\Twig_Environment $twig, $availableLocales)
    {
        $this->twig = $twig;
        $this->availableLocales = $availableLocales;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->twig->addGlobal('victoire_i18n_available_locales', $this->availableLocales);
    }
}
