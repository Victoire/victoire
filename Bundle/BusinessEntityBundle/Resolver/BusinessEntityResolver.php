<?php

namespace Victoire\Bundle\BusinessEntityBundle\Resolver;

use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIBusinessEntity;
use Victoire\Bundle\APIBusinessEntityBundle\Resolver\APIBusinessEntityResolver;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\ORMBusinessEntityBundle\Entity\ORMBusinessEntity;
use Victoire\Bundle\ORMBusinessEntityBundle\Resolver\ORMBusinessEntityResolver;

/*
 * Class BusinessEntityResolver.
 */
class BusinessEntityResolver implements BusinessEntityResolverInterface
{
    /**
     * @var ORMBusinessEntityResolver
     */
    private $ormResolver;
    /**
     * @var APIBusinessEntityResolver
     */
    private $apiResolver;

    public function __construct(ORMBusinessEntityResolver $ormResolver, APIBusinessEntityResolver $apiResolver)
    {
        $this->ormResolver = $ormResolver;
        $this->apiResolver = $apiResolver;
    }

    public function getBusinessEntity(EntityProxy $entityProxy)
    {
        return $this->findResolver($entityProxy->getBusinessEntity())->getBusinessEntity($entityProxy);
    }
    public function getBusinessEntities(BusinessEntity $businessEntity)
    {
        return $this->findResolver($businessEntity)->getBusinessEntities($businessEntity);
    }

    public function searchBusinessEntities(BusinessEntity $businessEntity, BusinessProperty $businessProperty, $filter)
    {
        return $this->findResolver($businessEntity)->searchBusinessEntities($businessEntity, $businessProperty, $filter);
    }

    protected function findResolver(BusinessEntity $businessEntity)
    {
        switch ($businessEntity->getType()) {
            case ORMBusinessEntity::TYPE:
                return $this->ormResolver;
            case APIBusinessEntity::TYPE:
                return $this->apiResolver;
        }
    }
}
