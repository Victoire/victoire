<?php

namespace Victoire\Bundle\BusinessEntityBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Container;
use Victoire\Bundle\BusinessEntityPageBundle\Repository\BusinessEntityPageRepository;

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
        $this->updateBusinessEntityPagesAndRegerateCache($eventArgs);
    }
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->updateBusinessEntityPagesAndRegerateCache($eventArgs);
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
    public function updateBusinessEntityPagesAndRegerateCache(LifecycleEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        $businessEntity = $this->container->get('victoire_core.helper.business_entity_helper')->findByEntityInstance($entity);

        if ($businessEntity) {
            $patterns = $entityManager->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPagePattern')->findPagePatternByBusinessEntity($businessEntity);
            foreach ($patterns as $pattern) {
                /** @var BusinessEntityPageRepository $bepRepo */
                $bepRepo = $entityManager->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPage');
                $computedPage = $this->container->get('victoire_business_entity_page.business_entity_page_helper')->generateEntityPageFromPattern($pattern, $entity);
                // Get the BusinessEntityPage if exists for the given entity
                $persistedPage = $bepRepo->findPageByBusinessEntityAndPattern($pattern, $entity, $businessEntity);
                // If there is diff between persisted BEP and computed, persist the change
                if ($persistedPage && $computedPage->getSlug() !== $persistedPage->getSlug()) {
                    $persistedPage->setSlug($computedPage->getSlug());
                    $entityManager->persist($persistedPage);
                    $entityManager->flush();

                    //we update the cache bor the persisted page
                    $this->updateCache($persistedPage, $entity);
                }else{
                    //we update cache with the computed page
                    $this->updateCache($pattern, $entity);

                }
            }
        }

    }

    protected function updateCache($pattern, $entity)
    {
        $this->container->get('victoire_core.view_cache_helper')->update($pattern, $entity);
    }

}
