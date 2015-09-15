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
     * bind to LoadClassMetadata method
     *
     * @return string[] The subscribed events
     */
    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
            'preRemove'
        );
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
            $viewCacheHelper->removeViewsReferencesByParameters(array(
                        'entityId' => $entity->getId(),
                        'entityNamespace' => get_class($entity),
            ));
        }
    }
    public function updateBusinessPagesAndRegerateCache(LifecycleEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $uow = $entityManager->getUnitOfWork();
        $entity = $eventArgs->getEntity();
        $businessEntity = $this->container->get('victoire_core.helper.business_entity_helper')->findByEntityInstance($entity);

        if ($businessEntity) {
            $patterns = $entityManager->getRepository('VictoireBusinessPageBundle:BusinessTemplate')->findPagePatternByBusinessEntity($businessEntity);
            foreach ($patterns as $pattern) {
                /** @var BusinessPageRepository $bepRepo */
                $bepRepo = $entityManager->getRepository('VictoireBusinessPageBundle:BusinessPage');
                $computedPage = $this->container->get('victoire_business_page.business_page_builder')->generateEntityPageFromPattern($pattern, $entity);
                // Get the BusinessPage if exists for the given entity
                $persistedPage = $bepRepo->findPageByBusinessEntityAndPattern($pattern, $entity, $businessEntity);
                // If there is diff between persisted BEP and computed, persist the change

                if ($persistedPage) {
                    $oldSlug = $persistedPage->getSlug();
                    $newSlug = $entity->getSlug();
                    $staticUrl = $persistedPage->getStaticUrl();

                    if ($staticUrl) {
                        $staticUrl = preg_replace('/'.$oldSlug.'/', $newSlug, $staticUrl);
                        $persistedPage->setStaticUrl($staticUrl);
                    }

                    $persistedPage->setSlug($computedPage->getSlug());


                    $entityManager->persist($persistedPage);
                    $entityManager->flush();

                    $viewReferences = $this->container->get('victoire_core.view_reference_builder')->buildViewReference($persistedPage, null, $entityManager);
                    //we update the cache bor the persisted page
                    $this->container->get('victoire_core.view_cache_helper')->update($viewReferences);
                } else {
                    $viewReferences = $this->container->get('victoire_core.view_reference_builder')->buildViewReference($computedPage, null, $entityManager);
                    //we update cache with the computed page
                    $this->container->get('victoire_core.view_cache_helper')->update($viewReferences);

                }
            }
        }

    }


}
