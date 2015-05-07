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
        $this->container= $container;
    }

    /**
     * bind to LoadClassMetadata method
     *
     * @return string[] The subscribed events
     */
    public function getSubscribedEvents()
    {
        return array(
            'postPersist' => 'updateBusinessEntityPagesAndRegerateCache',
            'postUpdate'  => 'updateBusinessEntityPagesAndRegerateCache',
        );
    }

    public function updateBusinessEntityPagesAndRegerateCache(LifecycleEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        $businessEntity = $this->container->get('victoire_business_entity.business_entity_helper')->findByEntityInstance($entity);

        if ($businessEntity) {
            $patterns = $entityManager->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPagePattern')->findPagePatternByBusinessEntity($businessEntity);
            foreach ($patterns as $pattern) {
                /** @var BusinessEntityPageRepository $bepRepo */
                $bepRepo = $entityManager->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPage');
                $computedPage = $this->container->get('victoire_business_entity_page.business_entity_page_helper')->generateEntityPageFromPattern($pattern, $entity);
                // Get the BusinessEntityPage if exists for the given entity
                $persistedPage = $bepRepo->findPageByBusinessEntityAndPattern($pattern, $entity, $businessEntity);
                // If there is diff between persisted BEP and computed, persist the change
                if ($persistedPage && $computedPage->getUrl() !== $persistedPage->getUrl()) {
                    $persistedPage->setUrl($computedPage->getUrl());
                    $entityManager->persist($persistedPage);
                    $entityManager->flush();
                }
                $this->updateCache($pattern, $entity);
            }
        }

    }

    protected function updateCache($pattern, $entity)
    {
        $this->container->get('victoire_core.view_cache_helper')->update($pattern, $entity);
    }

}
