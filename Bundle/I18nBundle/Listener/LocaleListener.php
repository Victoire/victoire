<?php

namespace Victoire\Bundle\I18nBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LocaleListener implements EventSubscriberInterface
{
    private $defaultLocale;

    /**
    * Constructor
    * @param $defaultLocale the default locale of the application
    */
    public function __construct($defaultLocale = 'fr')
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
    * @param GetResponseEvent $event
    *
    * method called on kernel request used only to persist locale in session
    */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }
        // on essaie de voir si la locale a été fixée dans le paramètre de routing _locale
        if ($locale = $request->getLocale()) {
            $request->getSession()->set('_locale', $locale);
            $request->setLocale($locale);
        } else {
            // si aucune locale n'a été fixée explicitement dans la requête, on utilise celle de la session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    /**
    * method to set the event suscribed by the listener 
    */
    public static function getSubscribedEvents()
    {
        return array(
            // doit être enregistré avant le Locale listener par défaut
            KernelEvents::REQUEST => array(array('onKernelRequest', 17)),
        );
    }
}