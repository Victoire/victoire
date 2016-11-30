<?php
namespace Victoire\Bundle\BusinessEntityBundle\Resolver;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;

/**
 * Interface BusinessEntityResolverInterface
 *
 * @package Bundle\BusinessEntityBundle\Resolver\Interface
 */
interface BusinessEntityResolverInterface
{
    public function getBusinessEntity(EntityProxy $entityProxy);
}
