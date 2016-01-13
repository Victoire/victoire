<?php

namespace Victoire\Bundle\ViewReferenceBundle\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\ViewReferenceBundle\Event\ViewReferenceEvent;
use Victoire\Bundle\ViewReferenceBundle\ViewReferenceEvents;

/**
 * Tracks if a slug changed and re-compute the view cache
 * ref: victoire_view_reference.event_subscriber.
 */
class ViewReferenceSubscriber implements \Doctrine\Common\EventSubscriber
{
    protected $businessPageBuilder;
    protected $viewReferenceProvider;
    protected $viewReferenceHelper;
    protected $dispatcher;

    /**
     * ViewReferenceSubscriber constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postUpdate,
            Events::postPersist,
            Events::preRemove,
        ];
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->updateViewReference($eventArgs);
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->updateViewReference($eventArgs);
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        // if a page is remove we remove his viewRef
        if ($entity instanceof WebViewInterface) {
            $event = new ViewReferenceEvent($entity);
            $this->dispatcher->dispatch(ViewReferenceEvents::REMOVE_VIEW_REFERENCE, $event);
        }
    }

    /**
     * This method dispatch the event that the view must be build/rebuild.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    private function updateViewReference(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        // if a page is persisted we rebuild his viewRef
        if ($entity instanceof WebViewInterface) {
            $event = new ViewReferenceEvent($entity);
            $this->dispatcher->dispatch(ViewReferenceEvents::UPDATE_VIEW_REFERENCE, $event);
        }
    }
}
