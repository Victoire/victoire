<?php

namespace Victoire\Bundle\BusinessPageBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;

/**
 * The Business Entity Page repository.
 */
class BusinessPageRepository extends EntityRepository
{
    /**
     *  Find the pagePatterns of the business entity.
     *
     * @param BusinessTemplate $pattern
     * @param Object $entity | int $entityId
     * @param BusinessEntity $businessEntity
     * @return mixed
     */
    public function findPageByBusinessEntityAndPattern(BusinessTemplate $pattern, $entity, BusinessEntity $businessEntity)
    {
        if(is_object($entity)){
            $entity = $entity->getId();
        }
        $qb = $this->createQueryBuilder('BusinessPage');
        $qb->join('BusinessPage.entityProxy', 'proxy');
        $qb->join('BusinessPage.template', 'template');
        $qb->join('proxy.'.$businessEntity->getId(), 'entity');

        $qb->where('template.id = :templateId');

        $qb->andWhere('entity.id = :entityId');

        $qb->setParameter(':templateId', $pattern);
        $qb->setParameter(':entityId', $entity);
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }
}
