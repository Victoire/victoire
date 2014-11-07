<?php

namespace Victoire\Bundle\BusinessEntityPageBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;

/**
 * The Business Entity Page repository
 */
class BusinessEntityPageRepository extends EntityRepository
{

    /**
     * Find the pagePatterns of the business entity
     * @param BusinessEntity $businessEntity
     *
     * @return array The list of pagePatterns
     */
    public function findPageByBusinessEntityAndPattern(BusinessEntityPagePattern $pattern, $entity, BusinessEntity $businessEntity)
    {

        $qb = $this->createQueryBuilder('businessEntityPage');
        $qb->join('businessEntityPage.entityProxy', 'proxy');
        $qb->join('businessEntityPage.template', 'template');
        $qb->join('proxy.' . $businessEntity->getId(), 'entity');

        $qb->where('template.id = :patternId');

        $qb->andWhere('entity.id = :entityId');

        $qb->setParameter(':patternId', $pattern);
        $qb->setParameter(':entityId', $entity->getId());
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }
}
