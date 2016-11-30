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
     * @param object           $entity         | int $entityId
     * @param BusinessEntity   $businessEntity
     *
     * @return mixed
     */
    public function findPageByBusinessEntityAndPattern(BusinessTemplate $pattern, $entity, BusinessEntity $businessEntity)
    {
        if (is_object($entity)) {
            $entity = $entity->getId();
        }
        $qb = $this->createQueryBuilder('BusinessPage');
        $qb->join('BusinessPage.entityProxy', 'proxy');
        $qb->join('BusinessPage.template', 'template');
        $qb->join('proxy.businessEntity', 'businessEntity');

        $qb->where('template.id = :templateId');

        $qb->andWhere('businessEntity.name = :entityName');
        $qb->andWhere('proxy.ressourceId = :entityId');

        $qb->setParameter(':templateId', $pattern);
        $qb->setParameter(':entityId', $entity);
        $qb->setParameter(':entityName', $businessEntity->getName());

        return $qb->getQuery()->getOneOrNullResult();
    }
}
