<?php

namespace Victoire\Bundle\I18nBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Victoire\Bundle\I18nBundle\Resolver\LocaleResolver;
use Victoire\Bundle\UserBundle\Model\VictoireUserInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;

    /**
     * Constructor.
     *
     * @param $defaultLocale the default locale of the application
     */
    public function __construct($defaultLocale)
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

        //see if the _locale routing parameter is set
        if ($locale = $request->getLocale()) {
            $request->getSession()->set('_locale', $locale);
            $request->setLocale($locale);
        } else {
            // use the session's one
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    /**
     * This method will be called on user login in order to set the victoire locale.
     *
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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
            KernelEvents::REQUEST             => 'onKernelRequest',
        ];
    }
}
