<?php

namespace Victoire\Bundle\ORMBusinessEntityBundle\Resolver;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;
use Victoire\Bundle\BusinessEntityBundle\Resolver\BusinessEntityResolverInterface;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\ORMBusinessEntityBundle\Entity\ORMBusinessEntity;

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

    public function getBusinessEntities(ORMBusinessEntity $businessEntity)
    {
        return $this->entityManager->getRepository($businessEntity->getClass())
            ->findAll();
    }

    /**
     * filter repo to get a list of entities.
     *
     * @param ORMBusinessEntity $businessEntity
     * @param array             $filter
     *
     * @return mixed
     */
    public function searchBusinessEntities(ORMBusinessEntity $businessEntity, BusinessProperty $businessProperty, $filter)
    {
        $alias = $businessEntity->getName();

        return $this->entityManager->getRepository($businessEntity->getClass())
            ->createQueryBuilder($alias)
            ->where($alias.'.'.$businessProperty->getName().'LIKE %:filter%')
            ->setParameter(':filter', $filter)
            ->getQuery()
            ->getResult();
    }
}
