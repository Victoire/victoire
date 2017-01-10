<?php

namespace Victoire\Bundle\BusinessEntityBundle\Provider;

use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;

class EntityProxyProvider
{
    public function getEntityProxy($entity, BusinessEntity $businessEntity, EntityManager $em)
    {
        $accessor = new PropertyAccessor();
        if (method_exists($entity, 'getId')) {
            $entityId = $entity->getId();
        } else {
            $entityId = $accessor->getValue($entity, $businessEntity->getBusinessIdentifiers()->first()->getName());
        }
        $entityProxy = $em->getRepository('Victoire\Bundle\CoreBundle\Entity\EntityProxy')->findOneBy(['ressourceId' => $entityId, 'businessEntity' => $businessEntity]);
        if (!$entityProxy) {
            $entityProxy = new EntityProxy();
            $entityProxy->setRessourceId($entityId);
            $entityProxy->setBusinessEntity($businessEntity);
        }
        $entityProxy->setEntity($entity);

        return $entityProxy;
    }
}
