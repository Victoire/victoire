<?php

namespace Victoire\Bundle\BusinessEntityBundle\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Builder\BusinessPageBuilder;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\BusinessPageBundle\Repository\BusinessPageRepository;
use Victoire\Bundle\ViewReferenceBundle\Event\ViewReferenceEvent;
use Victoire\Bundle\ViewReferenceBundle\ViewReferenceEvents;

class BusinessEntitySubscriber implements EventSubscriber
{
    protected $businessPageBuilder;
    protected $dispatcher;
    protected $businessEntityHelper;
    protected $flushedBusinessEntities;
    protected $businessPageHelper;
    protected $flushedBusinessTemplates;

    /**
     * @param BusinessPageBuilder      $businessPageBuilder
     * @param BusinessEntityHelper     $businessEntityHelper
     * @param BusinessPageHelper       $businessPageHelper
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(BusinessPageBuilder          $businessPageBuilder,
                                BusinessEntityHelper         $businessEntityHelper,
                                BusinessPageHelper           $businessPageHelper,
                                EventDispatcherInterface     $dispatcher
    ) {
        $this->businessPageBuilder = $businessPageBuilder;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->businessPageHelper = $businessPageHelper;
        $this->dispatcher = $dispatcher;
        $this->flushedBusinessEntities = new ArrayCollection();
        $this->flushedBusinessTemplates = new ArrayCollection();
    }

    /**
     * bind to LoadClassMetadata method.
     *
     * @return string[] The subscribed events
     */
    public function getSubscribedEvents()
    {
        return [
            'postUpdate',
            'postPersist',
            'preRemove',
            'postFlush',
        ];
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $eventArgs->getEntityManager();
        /** @var UnitOfWork $uow */
        $uow = $entityManager->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);
            if ($businessEntity) {
                $this->updateBusinessPages(
                    $entity,
                    $businessEntity,
                    $entityManager,
                    $uow->getScheduledEntityDeletions()
                );
            }
        }
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
     * get BusinessTemplate concerned by this entity (if so)
     * then get BusinessPages
     * for each BusinessPage, update its slug according to the new slug (if so).
     *
     * @param $entity
     * @param BusinessEntity $businessEntity
     * @param EntityManager  $entityManager
     * @param array          $deletions
     *
     * @throws \Exception
     *
     * @internal param LifecycleEventArgs $eventArgs
     */
    public function updateBusinessPages($entity, BusinessEntity $businessEntity, EntityManager $entityManager, $deletions)
    {
        $businessTemplates = $entityManager->getRepository('VictoireBusinessPageBundle:BusinessTemplate')->findPagePatternByBusinessEntity($businessEntity);
        foreach ($businessTemplates as $businessTemplate) {
            // we generate viewRef for each BT translation
            /** @var ViewTra $translation */
            foreach ($businessTemplate->getTranslations() as $translation) {
                $businessTemplate->setCurrentLocale($translation->getLocale());
                if ($this->businessPageHelper->isEntityAllowed($businessTemplate, $entity, $entityManager)) {
                    /** @var BusinessPageRepository $bepRepo */
                    $bepRepo = $entityManager->getRepository('VictoireBusinessPageBundle:BusinessPage');
                    $virtualBusinessPage = $this->businessPageBuilder->generateEntityPageFromTemplate(
                        $businessTemplate,
                        $entity,
                        $entityManager
                    );
                    // Get the BusinessPage if exists for the given entity
                    /** @var BusinessPage $businessPage */
                    $businessPage = $bepRepo->findPageByBusinessEntityAndPattern(
                        $businessTemplate,
                        $entity,
                        $businessEntity
                    );
                    $businessPage->setCurrentLocale($translation->getLocale());
                    // If there is diff between persisted BEP and computed, persist the change
                    $scheduledForRemove = false;
                    foreach ($deletions as $deletion) {
                        if (get_class($deletion) == get_class($businessPage)
                            && $deletion->getId() === $businessPage->getId()
                        ) {
                            $scheduledForRemove = true;
                        }
                    }

                    if ($businessPage && !$scheduledForRemove) {
                        $oldSlug = $businessPage->getSlug();
                        $newSlug = $entity->getSlug();
                        $businessPage->setName($virtualBusinessPage->getName());
                        $businessPage->setSlug($virtualBusinessPage->getSlug());

                        $entityManager->persist($businessPage);
                        $entityManager->flush();
                    }
                }
            }
        }
    }

    /**
     * Iterate over inserted BusinessEntities and BusinessTemplates catched by postPersist
     * and dispatch event to generate the needed ViewReferences.
     *
     * @param PostFlushEventArgs $eventArgs
     *
     * @throws \Exception
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        foreach ($this->flushedBusinessEntities as $entity) {
            $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);
            //find all BT that can represent the businessEntity
            $businessTemplates = $em->getRepository('VictoireBusinessPageBundle:BusinessTemplate')->findPagePatternByBusinessEntity($businessEntity);
            foreach ($businessTemplates as $businessTemplate) {
                // we generate viewRef for each BT translation
                foreach ($businessTemplate->getTranslations() as $translation) {
                    $businessTemplate->setCurrentLocale($translation->getLocale());

                    if ($page = $em->getRepository(
                        'Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage'
                    )->findPageByBusinessEntityAndPattern($businessTemplate, $entity, $businessEntity)
                    ) {
                        //if it's a BP we update the BP
                        $this->businessPageBuilder->updatePageParametersByEntity($page, $entity);
                    } else {
                        $page = $this->businessPageBuilder->generateEntityPageFromTemplate(
                            $businessTemplate,
                            $entity,
                            $em
                        );
                    }
                    if ($this->businessPageHelper->isEntityAllowed($businessTemplate, $entity, $em)) {
                        //update the reference
                        $event = new ViewReferenceEvent($page);
                        $this->dispatcher->dispatch(ViewReferenceEvents::UPDATE_VIEW_REFERENCE, $event);
                    }
                }
            }
        }

        foreach ($this->flushedBusinessTemplates as $entity) {
            $businessEntityId = $entity->getBusinessEntityId();
            $businessEntity = $this->businessEntityHelper->findById($businessEntityId);
            //find all entities
            $entities = $this->businessPageHelper->getEntitiesAllowed($entity, $em);
            // we generate viewRef for each BT translation
            foreach ($entity->getTranslations() as $translation) {
                $entity->setCurrentLocale($translation->getLocale());
                foreach ($entities as $be) {
                    if ($this->businessPageHelper->isEntityAllowed($entity, $be, $em)) {
                        if ($page = $em->getRepository(
                            'Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage'
                        )->findPageByBusinessEntityAndPattern($entity, $be, $businessEntity)
                        ) {
                            //rebuild page if its a BP
                            $this->businessPageBuilder->updatePageParametersByEntity($page, $be);
                        } else {
                            $page = $this->businessPageBuilder->generateEntityPageFromTemplate(
                                $entity,
                                $be,
                                $em
                            );
                        }
                        // update reference
                        $event = new ViewReferenceEvent($page);
                        $this->dispatcher->dispatch(ViewReferenceEvents::UPDATE_VIEW_REFERENCE, $event);
                    }
                }
            }
        }
        $this->flushedBusinessEntities->clear();
        $this->flushedBusinessTemplates->clear();
    }

    /**
     * This method throw an event if needed for a view related to a businessEntity.
     *
     * @param LifecycleEventArgs $eventArgs
     *
     * @throws \Exception
     */
    private function updateViewReference(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        //if it's a businessEntity we need to rebuild virtuals (BPs are rebuild in businessEntitySubscriber)
        if ($businessEntity = $this->businessEntityHelper->findByEntityInstance($entity)) {
            $this->flushedBusinessEntities->add($entity);
        }
        //if it a businessTemplate we have to rebuild virtuals or update BP
        if ($entity instanceof BusinessTemplate) {
            $this->flushedBusinessTemplates->add($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     *
     * @throws \Exception
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        //if we remove a BP we need to remplace by a VBP ref
        if ($entity instanceof BusinessPage) {
            //remove BP ref
            $event = new ViewReferenceEvent($entity);
            $this->dispatcher->dispatch(ViewReferenceEvents::REMOVE_VIEW_REFERENCE, $event);
            $em = $eventArgs->getEntityManager();
            $businessTemplate = $entity->getTemplate();
            $page = $this->businessPageBuilder->generateEntityPageFromTemplate(
                $businessTemplate,
                $entity->getBusinessEntity(),
                $em
            );
            //create VBP ref
            //TODO :: dont rebuild if businessEntity or businessTemplate doesn't exist
            $event = new ViewReferenceEvent($page);
            $this->dispatcher->dispatch(ViewReferenceEvents::UPDATE_VIEW_REFERENCE, $event);
        }

        //if it's a businessEntity, we need to remove all BP and VBP ref
        if ($businessEntity = $this->businessEntityHelper->findByEntityInstance($entity)) {
            $em = $eventArgs->getEntityManager();
            $businessTemplates = $em->getRepository('VictoireBusinessPageBundle:BusinessTemplate')->findPagePatternByBusinessEntity($businessEntity);
            foreach ($businessTemplates as $businessTemplate) {

                // we generate viewRef for each BT translation
                foreach ($businessTemplate->getTranslations() as $translation) {
                    $businessTemplate->setCurrentLocale($translation->getLocale());
                    if ($page = $em->getRepository('Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage')->findPageByBusinessEntityAndPattern($businessTemplate, $entity, $businessEntity)) {
                        $event = new ViewReferenceEvent($page);
                        $this->dispatcher->dispatch(ViewReferenceEvents::REMOVE_VIEW_REFERENCE, $event);
                    } else {
                        $page = $this->businessPageBuilder->generateEntityPageFromTemplate(
                            $businessTemplate,
                            $entity,
                            $em
                        );
                        $event = new ViewReferenceEvent($page);
                        $this->dispatcher->dispatch(ViewReferenceEvents::REMOVE_VIEW_REFERENCE, $event);
                    }
                }
            }
        }
        //if we remove a businessTemplate remove all VBT ref (BP cascade remove)
        if ($entity instanceof BusinessTemplate) {
            $em = $eventArgs->getEntityManager();
            $entities = $this->businessPageHelper->getEntitiesAllowed($entity, $em);

            // we generate viewRef for each BT translation
            foreach ($entity->getTranslations() as $translation) {
                $entity->setCurrentLocale($translation->getLocale());
                foreach ($entities as $be) {
                    $page = $this->businessPageBuilder->generateEntityPageFromTemplate(
                        $entity,
                        $be,
                        $em
                    );
                    $event = new ViewReferenceEvent($page);
                    $this->dispatcher->dispatch(ViewReferenceEvents::REMOVE_VIEW_REFERENCE, $event);
                }
            }
        }
    }
}
