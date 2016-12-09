<?php

namespace Victoire\Bundle\ORMBusinessEntityBundle\Resolver;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessEntityBundle\Resolver\BusinessEntityResolverInterface;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;

/**
 * Class ORMBusinessEntityResolver.
 */
class ORMBusinessEntityResolver implements BusinessEntityResolverInterface
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getBusinessEntity(EntityProxy $entityProxy)
    {
        return $this->entityManager->getRepository($entityProxy->getBusinessEntity()->getClass())
            ->findOneById($entityProxy->getRessourceId());
    }
}
