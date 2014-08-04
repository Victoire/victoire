<?php

namespace Victoire\Bundle\BusinessEntityPageBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

/**
 * The Business Entity PagePattern repository
 */
class BusinessEntityPagePatternRepository extends EntityRepository
{
    /**
     * Find the pagePatterns of the business entity
     * @param BusinessEntity $businessEntity
     *
     * @return array The list of pagePatterns
     */
    public function findPagePatternByBusinessEntity(BusinessEntity $businessEntity)
    {

        $qb = $this->createQueryBuilder('businessEntitiesPagePattern');
        $qb->where('businessEntitiesPagePattern.businessEntityName = :businessEntityName');

        $qb->setParameter(':businessEntityName', $businessEntity->getId());

        $qb->orderBy('businessEntitiesPagePattern.updatedAt', 'ASC');

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    /**
     * Find the business entity page pattern that looks like this url
     * @param string $url
     *
     * @return array The list of pagePatterns
     */
    public function findOneByLikeUrl($url)
    {
        $pagePattern = null;

        $qb = $this->createQueryBuilder('businessEntitiesPagePattern');
        $qb->where($qb->expr()->like('businessEntitiesPagePattern.url', $qb->expr()->literal($url)));

        $qb->orderBy('businessEntitiesPagePattern.updatedAt', 'ASC');

        $results = $qb->getQuery()->getResult();

        if (count($results) > 0) {
            $pagePattern = $results[0];
        }

        return $pagePattern;
    }
}
