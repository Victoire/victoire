<?php

namespace Victoire\Bundle\SeoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * Class HttpErrorRepository.
 */
class HttpErrorRepository extends EntityRepository
{
    use StateFullRepositoryTrait;

    /**
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
     * @param string $order
     * @param string $direction
     *
     * @return QueryBuilder
     */
    public function getUnresolvedQuery($order = 'error.counter', $direction = 'DESC')
    {
        $this->getAll(true);

        return $this->qb->orderBy($order, $direction);
    }
}