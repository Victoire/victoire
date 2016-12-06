<?php

namespace Victoire\Bundle\BusinessEntityBundle\Resolver;

use Victoire\Bundle\CoreBundle\Entity\EntityProxy;

/**
 * Interface BusinessEntityResolverInterface.
 */
interface BusinessEntityResolverInterface
{
    public function getBusinessEntity(EntityProxy $entityProxy);
}
