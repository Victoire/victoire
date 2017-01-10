<?php

namespace Victoire\Bundle\BusinessEntityBundle\Resolver;

use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;

/*
 * Class BusinessEntityResolver.
 */
class BusinessEntityResolver implements BusinessEntityResolverInterface
{
    private $resolvers;

    public function __construct()
    {
        $this->resolvers = [];
    }

    public function addResolver($resolver, $type)
    {
        $this->resolvers[$type] = $resolver;
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
        if (array_key_exists($businessEntity->getType(), $this->resolvers)) {
            return $this->resolvers[$businessEntity->getType()];
        } else {
            throw new \Exception(sprintf('there is no resolver for %s type', $businessEntity->getType()));
        }
    }
}
