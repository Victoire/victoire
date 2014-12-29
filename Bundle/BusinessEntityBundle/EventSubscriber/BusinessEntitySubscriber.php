<?php

namespace Victoire\Bundle\BusinessEntityBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Container;

class BusinessEntitySubscriber implements EventSubscriber
{

    private $container;

    /**
     * @param Container $container @victoire_core.helper.business_entity_helper
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * bind to LoadClassMetadata method
     *
     * @return array The subscribed events
     */
    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
        );
    }

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->updateBusinessEntityPagesAndRegerateCache($eventArgs->getEntity());
    }
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->updateBusinessEntityPagesAndRegerateCache($eventArgs->getEntity());
    }

    protected function updateBusinessEntityPagesAndRegerateCache($entity)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $businessEntities = array();
        $businessEntitiesArray = array();
        $businessEntities = $this->container->get('victoire_core.helper.business_entity_helper')->getBusinessEntities();
        foreach ($businessEntities as $businessEntity) {
            $businessEntitiesArray[$businessEntity->getClass()] = $businessEntity;
        }
        $entityClass = $em->getClassMetadata(get_class($entity))->getName();
        if (array_key_exists($entityClass, $businessEntitiesArray)) {
            $businessEntity = $businessEntitiesArray[$entityClass];
            $em = $this->container->get('doctrine.orm.entity_manager');
            $patterns = $em->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPagePattern')->findPagePatternByBusinessEntity($businessEntity);
            foreach ($patterns as $pattern) {
                $this->updateBusinessEntityPage($pattern, $entity, $businessEntity);
                $this->updateCache($pattern, $entity);
            }
        }

    }

    protected function updateBusinessEntityPage($pattern, $entity, $businessEntity)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $bepRepo = $em->getRepository('Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage');
        $computedPage = $this->container->get('victoire_business_entity_page.business_entity_page_helper')->generateEntityPageFromPattern($pattern, $entity);
        // Get the BusinessEntityPage if exists for the given entity
        $persistedPage = $bepRepo->findPageByBusinessEntityAndPattern($pattern, $entity, $businessEntity);
        // If there is diff netween persisted BEP and computed, persist the change
        if ($persistedPage && $computedPage->getUrl() !== $persistedPage->getUrl()) {
            $persistedPage->setUrl($computedPage->getUrl());
            $em->persist($persistedPage);
            $em->flush();
        }

    }
    protected function updateCache($pattern, $entity)
    {
        $this->container->get('victoire_core.view_cache_helper')->update($pattern, $entity);
    }

}
