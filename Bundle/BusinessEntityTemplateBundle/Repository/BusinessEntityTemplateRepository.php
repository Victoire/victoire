<?php

namespace Victoire\Bundle\BusinessEntityTemplateBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\CoreBundle\Entity\BusinessEntity;


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
        $qb->where('businessEntityTemplate.businessEntityId = :businessEntityId');

        $qb->setParameter(':businessEntityId', $businessEntity->getId());

        $qb->orderBy('businessEntityTemplate.updatedAt', 'ASC');

        $results = $qb->getQuery()->getResult();

        return $results;
    }
}