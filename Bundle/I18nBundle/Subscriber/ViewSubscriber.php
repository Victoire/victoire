<?php

namespace Victoire\Bundle\I18nBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\I18nBundle\Resolver\LocaleResolver;

class ViewSubscriber implements EventSubscriber
{
    private $defaultLocale;
    private $localeResolver;

    /**
     * Constructor
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
    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof View && $entity->getLocale() == null) {
            $entity->setLocale($this->defaultLocale);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
        );
    }
}
