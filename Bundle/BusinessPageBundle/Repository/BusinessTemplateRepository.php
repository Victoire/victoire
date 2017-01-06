<?php

namespace Victoire\Bundle\BusinessPageBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

/**
 * The Business Entity PagePattern repository.
 */
class BusinessTemplateRepository extends EntityRepository
{
    private $queryBuilder;

    /**
     * Get query builder instance.
     */
    public function getInstance()
    {
        return $this->queryBuilder ? $this->queryBuilder : $this->createQueryBuilder('pattern');
    }

    /**
     * Find the pagePatterns for the business entity.
     *
     * @param BusinessEntity $businessEntity
     *
     * @return array The list of pagePatterns
     */
    public function findPagePatternByBusinessEntity(BusinessEntity $businessEntity)
    {
        return $this->getPagePatternByBusinessEntity($businessEntity)
            ->getQuery()
            ->getResult();
    }

    /**
     * get the pagePatterns for the business entity query.
     *
     * @param string $businessEntityId
     *
     * @return QueryBuilder
     */
    public function getPagePatternByBusinessEntity($businessEntity)
    {
        return $this->createQueryBuilder('BusinessTemplate')
            ->where('BusinessTemplate.businessEntity = :businessEntity')
            ->setParameter(':businessEntity', $businessEntity)
            ->orderBy('BusinessTemplate.updatedAt', 'ASC');
    }

    /**
     * Find the business entity page pattern that looks like this url.
     *
     * @param string $url
     *
     * @return array The list of pagePatterns
     */
    public function findOneByLikeUrl($url)
    {
        $pagePattern = null;

        $qb = $this->createQueryBuilder('BusinessTemplate');
        $qb->where($qb->expr()->like('BusinessTemplate.url', $qb->expr()->literal($url)));

        $qb->orderBy('BusinessTemplate.updatedAt', 'ASC');

        $results = $qb->getQuery()->getResult();

        if (count($results) > 0) {
            $pagePattern = $results[0];
        }

        return $pagePattern;
    }
}
