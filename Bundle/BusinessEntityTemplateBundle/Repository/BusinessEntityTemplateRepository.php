<?php

namespace Victoire\Bundle\BusinessEntityTemplateBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

/**
 *
 * @author Thomas Beaujean
 *
 */
class BusinessEntityTemplateRepository extends EntityRepository
{
    /**
     * Find the templates of the business entity
     *
     * @param BusinessEntity $businessEntity
     *
     * @return array The list of templates
     */
    public function findTemplatesByBusinessEntity(BusinessEntity $businessEntity)
    {

        $qb = $this->createQueryBuilder('businessEntityTemplate');
        $qb->where('businessEntityTemplate.businessEntityName = :businessEntityName');

        $qb->setParameter(':businessEntityName', $businessEntity->getId());

        $qb->orderBy('businessEntityTemplate.updatedAt', 'ASC');

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    /**
     * Find the business entity template that looks like this url
     *
     * @param string $url
     *
     * @return array The list of templates
     */
    public function findOneByLikeUrl($url)
    {
        $template = null;

        $qb = $this->createQueryBuilder('businessEntityTemplate');
        $qb->where($qb->expr()->like('businessEntityTemplate.url', $qb->expr()->literal($url)));

        $qb->orderBy('businessEntityTemplate.updatedAt', 'ASC');

        $results = $qb->getQuery()->getResult();

        if (count($results) > 0) {
            $template = $results[0];
        }

        return $template;
    }
}
