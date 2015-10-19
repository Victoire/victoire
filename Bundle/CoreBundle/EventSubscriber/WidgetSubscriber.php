<?php

namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

class WidgetSubscriber implements EventSubscriber
{

    protected $views = array();

    public function getSubscribedEvents()
    {
        return array(
            'preUpdate',
            'postFlush'
        );
    }

    /**
     * Change cssHash of views when a widget is updated
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
            if (!($entity instanceof Widget)) {
                continue;
            }

            $view = $entity->getView();
            $view->changeCssHash();
            $this->views[] = $view;
        }
    }

    /**
     * Persist and flush updated views
     *
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        if(!empty($this->views)) {

            $em = $event->getEntityManager();

            foreach ($this->views as $view) {
                $em->persist($view);
            }

            $this->views = array();
            $em->flush();
        }
    }
}