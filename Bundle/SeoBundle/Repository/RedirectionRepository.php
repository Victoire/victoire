<?php

namespace Victoire\Bundle\SeoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * Class RedirectionRepository.
 */
class RedirectionRepository extends EntityRepository
{
    use StateFullRepositoryTrait;

    /**
     * Get all redirections in the repository.
     *
     * @return RedirectionRepository
     */
    public function getAll()
    {
        $this->clearInstance();
        $this->qb = $this->getInstance();

        return $this;
    }

    /**
     * Get all redirections in the repository.
     *
     * @param string $order
     * @param string $direction
     *
     * @return QueryBuilder
     */
    public function getUnresolvedQuery($order = 'redirection.counter', $direction = 'DESC')
    {
        $this->getAll();

        return $this->qb->orderBy($order, $direction);
    }
}
