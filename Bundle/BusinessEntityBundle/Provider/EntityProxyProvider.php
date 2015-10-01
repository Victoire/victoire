<?php

namespace Victoire\Bundle\BusinessEntityBundle\Provider;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;

class EntityProxyProvider
{
    public function getEntityProxy($entity, BusinessEntity $businessEntity, EntityManager $em)
    {
        $entityProxy = $em->getRepository('Victoire\Bundle\CoreBundle\Entity\EntityProxy')->findOneBy([$businessEntity->getId() => $entity]);
        if (!$entityProxy) {
            $entityProxy = new EntityProxy();
            $entityProxy->setEntity($entity, $businessEntity->getName());
        }

        return $entityProxy;
    }
}
