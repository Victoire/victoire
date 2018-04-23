<?php

namespace Victoire\Bundle\ConfigBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * The Business Entity Page repository.
 */
class GlobalConfigRepository extends EntityRepository
{
    /**
     *  Find the last GlobalConfig entry.
     *
     * @return GlobalConfig|null
     */
    public function findLast()
    {
        return $this->createQueryBuilder('global_config')
            ->leftJoin('global_config.logo', 'logo')->addSelect('logo')
            ->orderBy('global_config.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
