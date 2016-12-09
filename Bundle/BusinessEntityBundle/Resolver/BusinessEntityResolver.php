<?php

namespace Victoire\Bundle\BusinessEntityBundle\Resolver;

use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIBusinessEntity;
use Victoire\Bundle\APIBusinessEntityBundle\Resolver\APIBusinessEntityResolver;
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
        switch ($entityProxy->getBusinessEntity()->getType()) {
            case ORMBusinessEntity::TYPE:
                return $this->ormResolver->getBusinessEntity($entityProxy);
            case APIBusinessEntity::TYPE:
                return $this->apiResolver->getBusinessEntity($entityProxy);
        }
    }
}
