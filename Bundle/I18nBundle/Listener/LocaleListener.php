<?php

namespace Victoire\Bundle\I18nBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Victoire\Bundle\I18nBundle\Resolver\LocaleResolver;
use Victoire\Bundle\UserBundle\Model\VictoireUserInterface;

class LocaleListener implements EventSubscriberInterface
{
    private $defaultLocale;
    private $localeResolver;

    /**
     * Constructor
     * @param $defaultLocale the default locale of the application
     */
    public function __construct($defaultLocale, LocaleResolver $localeResolver)
    {
        $this->defaultLocale = $defaultLocale;
        $this->localeResolver = $localeResolver;
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

        $locale = $this->localeResolver->resolve($request);

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
     * This method will be called on user login in order to set the victoire locale
     * @param InteractiveLoginEvent $event
     */
    public function onLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof VictoireUserInterface) {
            // set the victoireLocale
            $event->getRequest()->getSession()->set('victoire_locale', $user->getLocale());
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
            KernelEvents::REQUEST             => 'onKernelRequest'
        );
    }
}
