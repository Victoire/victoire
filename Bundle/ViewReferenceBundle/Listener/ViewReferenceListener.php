<?php

namespace Victoire\Bundle\ViewReferenceBundle\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Victoire\Bundle\ViewReferenceBundle\Builder\ViewReferenceBuilder;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceManager;
use Victoire\Bundle\ViewReferenceBundle\Event\ViewReferenceEvent;
use Victoire\Bundle\ViewReferenceBundle\ViewReferenceEvents;

/**
 * Class ViewReferenceListener.
 */
class ViewReferenceListener implements EventSubscriberInterface
{
    private $viewReferenceBuilder;
    private $viewReferenceManager;
    private $em;

    /**
     * ViewReferenceListener constructor.
     *
     * @param ViewReferenceBuilder   $viewReferenceBuilder
     * @param ViewReferenceManager   $viewReferenceManager
     * @param EntityManagerInterface $em
     */
    public function __construct(ViewReferenceBuilder $viewReferenceBuilder, ViewReferenceManager $viewReferenceManager, EntityManagerInterface $em)
    {
        $this->viewReferenceBuilder = $viewReferenceBuilder;
        $this->viewReferenceManager = $viewReferenceManager;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ViewReferenceEvents::UPDATE_VIEW_REFERENCE => 'updateViewReference',
            ViewReferenceEvents::REMOVE_VIEW_REFERENCE => 'removeViewReference',
        ];
    }

    /**
     * This method is call when a viewReference need to be update.
     *
     * @param ViewReferenceEvent $event
     */
    public function updateViewReference(ViewReferenceEvent $event)
    {
        $view = $event->getView();
        $viewReference = $this->viewReferenceBuilder->buildViewReference($view, $this->em);
        $this->viewReferenceManager->saveReference($viewReference);
    }

    /**
     * This method is call when a viewReference need to be remove.
     *
     * @param ViewReferenceEvent $event
     */
    public function removeViewReference(ViewReferenceEvent $event)
    {
        $view = $event->getView();
        $viewReference = $this->viewReferenceBuilder->buildViewReference($view, $this->em);
        $this->viewReferenceManager->removeReference($viewReference);
    }
}
