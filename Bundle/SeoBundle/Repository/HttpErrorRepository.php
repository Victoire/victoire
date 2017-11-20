<?php

namespace Victoire\Bundle\SeoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * Class HttpErrorRepository.
 *
 * @package SeoBundle\Repository
 */
class HttpErrorRepository extends EntityRepository
{
    use StateFullRepositoryTrait;

    /**
     * Get all redirections in the repository.
     *
     * @param bool $exceptRedirected
     *
     * @return HttpErrorRepository
     */
    public function getAll($exceptRedirected = false)
    {
        $this->clearInstance();

        $this->qb = $this->getInstance('error');

        if (true === $exceptRedirected) {
            $this->qb->andWhere('error.redirection IS NULL');
        }

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
    public function getUnresolvedQuery($order = 'error.counter', $direction = 'DESC')
    {
        $this->getAll(true);

        return $this->qb;
        //->orderBy($order, $direction);
    }
}