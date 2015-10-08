<?php

namespace Victoire\Bundle\BusinessEntityBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Container;
use Victoire\Bundle\BusinessPageBundle\Repository\BusinessPageRepository;

class BusinessEntitySubscriber implements EventSubscriber
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * bind to LoadClassMetadata method.
     *
     * @return string[] The subscribed events
     */
    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'postUpdate',
            'preRemove',
        ];
    }

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->updateBusinessPagesAndRegerateCache($eventArgs);
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->updateBusinessPagesAndRegerateCache($eventArgs);
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $businessEntity = $this->container->get('victoire_core.helper.business_entity_helper')->findByEntityInstance($entity);
        if ($businessEntity) {
            $viewCacheHelper = $this->container->get('victoire_core.view_cache_helper');
            //remove all references which refer to the entity
            $viewCacheHelper->removeViewsReferencesByParameters([[
                        'entityId'        => $entity->getId(),
                        'entityNamespace' => get_class($entity),
            ]]);
        }
    }

    public function updateBusinessPagesAndRegerateCache(LifecycleEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        $businessEntity = $this->container->get('victoire_core.helper.business_entity_helper')->findByEntityInstance($entity);

        if ($businessEntity) {
            $businessTemplates = $entityManager->getRepository('VictoireBusinessPageBundle:BusinessTemplate')->findPagePatternByBusinessEntity($businessEntity);
            foreach ($businessTemplates as $businessTemplate) {
                if ($this->container->get('victoire_business_page.business_page_helper')->isEntityAllowed($businessTemplate, $entity, $entityManager)) {
                    /** @var BusinessPageRepository $bepRepo */
                    $bepRepo = $entityManager->getRepository('VictoireBusinessPageBundle:BusinessPage');
                    $virtualBusinessPage = $this->container->get(
                        'victoire_business_page.business_page_builder'
                    )->generateEntityPageFromTemplate($businessTemplate, $entity, $entityManager);
                    // Get the BusinessPage if exists for the given entity
                    $businessPage = $bepRepo->findPageByBusinessEntityAndPattern(
                        $businessTemplate,
                        $entity,
                        $businessEntity
                    );
                    // If there is diff between persisted BEP and computed, persist the change
                    $scheduledForRemove = false;
                    foreach ($eventArgs->getEntityManager()->getUnitOfWork()->getScheduledEntityDeletions(
                    ) as $deletion) {
                        if (get_class($deletion) == get_class($businessPage)
                            && $deletion->getId() === $businessPage->getId()) {
                            $scheduledForRemove = true;
                        }
                    }

                    if ($businessPage && !$scheduledForRemove) {
                        $oldSlug = $businessPage->getSlug();
                        $newSlug = $entity->getSlug();
                        $staticUrl = $businessPage->getStaticUrl();

                        if ($staticUrl) {
                            $staticUrl = preg_replace('/'.$oldSlug.'/', $newSlug, $staticUrl);
                            $businessPage->setStaticUrl($staticUrl);
                        }

                        $businessPage->setName($virtualBusinessPage->getName());
                        $businessPage->setSlug($virtualBusinessPage->getSlug());

                        $entityManager->persist($businessPage);
                        $entityManager->flush();

                        $viewReferences = $this->container->get(
                            'victoire_core.view_reference_builder'
                        )->buildViewReference($businessPage, $entityManager);
                        //we update the cache bor the persisted page
                    } else {
                        $viewReferences = $this->container->get(
                            'victoire_core.view_reference_builder'
                        )->buildViewReference($virtualBusinessPage, $entityManager);
                        //we update cache with the computed page
                    }
                    $this->container->get('victoire_core.view_cache_helper')->update($viewReferences);
                } else {
                    $rootNode = $this->container->get('victoire_core.view_cache_helper')->readCache();

                    $parameters = [
                        'patternId'     => $businessTemplate->getId(),
                        'entityId'      => $entity->getId(),
                        'viewNamespace' => 'Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage',
                    ];

                    $viewReferenceHelper = $this->container->get('victoire_view_reference.helper');
                    $viewsReferencesToRemove = $this->container->get('victoire_core.view_cache_helper')->getAllReferenceByParameters($parameters);
                    foreach ($viewsReferencesToRemove as $viewReferenceToRemove) {
                        $viewReferenceHelper->removeViewReference($rootNode, $viewReferenceToRemove);
                    }

                    $viewReferences = $this->container->get('victoire_view_reference.helper')->convertXmlCacheToArray($rootNode);
                    $this->container->get('victoire_core.view_cache_helper')->write($viewReferences);
                }
            }
        }
    }
}
