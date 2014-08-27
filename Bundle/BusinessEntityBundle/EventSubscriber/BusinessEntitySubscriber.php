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
        $this->updateCache($eventArgs->getEntity());
    }
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->updateCache($eventArgs->getEntity());
    }

    protected function updateCache($entity)
    {

        $businessEntities = array();
        $businessEntities = $this->container->get('victoire_core.helper.business_entity_helper')->getBusinessEntities();
        foreach ($businessEntities as $businessEntity) {
            $businessEntities[$businessEntity->getClass()] = $businessEntity;
        }

        if (array_key_exists(get_class($entity), $businessEntities)) {
            $businessEntity = $businessEntities[get_class($entity)];
            $em = $this->container->get('doctrine.orm.entity_manager');
            $patterns = $em->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPagePattern')->findPagePatternByBusinessEntity($businessEntity);
            foreach ($patterns as $pattern) {
                $page = $this->container->get('victoire_business_entity_page.business_entity_page_helper')->generateEntityPageFromPattern($pattern, $entity);
                $this->container->get('victoire_core.view_cache_helper')->updatePageCache($page, $entity);
            }
        }
    }

}
