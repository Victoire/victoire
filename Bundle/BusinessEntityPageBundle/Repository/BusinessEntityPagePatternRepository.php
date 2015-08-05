<?php

namespace Victoire\Bundle\BusinessEntityPageBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

/**
 * The Business Entity PagePattern repository
 */
class BusinessEntityPagePatternRepository extends EntityRepository
{
    private $queryBuilder;

    /**
     * Get query builder instance
     */
    public function getInstance()
    {
        return $this->queryBuilder ? $this->queryBuilder : $this->createQueryBuilder('pattern');
    }

    /**
     * Find the pagePatterns for the business entity
     * @param BusinessEntity $businessEntity
     *
     * @return array The list of pagePatterns
     */
    public function findPagePatternByBusinessEntity(BusinessEntity $businessEntity)
    {
        return $this->getPagePatternByBusinessEntity($businessEntity->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * get the pagePatterns for the business entity query
     * @param string $entityName
     *
     * @return QueryBuilder
     */
    public function getPagePatternByBusinessEntity($entityName)
    {
        return $this->createQueryBuilder('businessEntityPagePattern')
            ->where('businessEntityPagePattern.businessEntityName = :entityName')
            ->setParameter(':entityName', $entityName)
            ->orderBy('businessEntityPagePattern.updatedAt', 'ASC');
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

        $qb = $this->createQueryBuilder('businessEntityPagePattern');
        $qb->where($qb->expr()->like('businessEntityPagePattern.url', $qb->expr()->literal($url)));

        $qb->orderBy('businessEntityPagePattern.updatedAt', 'ASC');

        $results = $qb->getQuery()->getResult();

        if (count($results) > 0) {
            $pagePattern = $results[0];
        }

        return $pagePattern;
    }
}
