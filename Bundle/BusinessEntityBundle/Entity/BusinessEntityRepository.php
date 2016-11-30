<?php

namespace Victoire\Bundle\BusinessEntityBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * The BusinessEntity Repository.
 */
class BusinessEntityRepository extends EntityRepository
{
    /**
     * @param array $widgetType
     */
    public function findByAvailableWidgets($widgetType)
    {
        return $this->createQueryBuilder('be')
            ->where('be.availableWidgets LIKE :widgetType')
            ->setParameter(':widgetType', '%'. $widgetType . '%')
            ->getQuery()
            ->execute();
    }
}
