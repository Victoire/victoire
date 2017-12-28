<?php

namespace Victoire\Bundle\SeoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;
use Victoire\Bundle\SeoBundle\Entity\HttpError;

/**
 * Class HttpErrorRepository.
 */
class HttpErrorRepository extends EntityRepository
{
    use StateFullRepositoryTrait;

    /**
     * Get every errors in the repository.
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
     * Get every route errors in the repository.
     *
     * @param string $order
     * @param string $direction
     *
     * @return QueryBuilder
     */
    public function getRouteErrors($order = 'error.counter', $direction = 'DESC')
    {
        $this->getAll(true);

        /** @var QueryBuilder $qb */
        $qb = $this->qb;

        return $qb
            ->andWhere('error.type = :type')
            ->setParameter('type', HttpError::TYPE_ROUTE)
            ->orderBy($order, $direction);
    }

    /**
     * Get every file errors in the repository.
     *
     * @param string $order
     * @param string $direction
     *
     * @return QueryBuilder
     */
    public function getFileErrors($order = 'error.counter', $direction = 'DESC')
    {
        $this->getAll(true);

        /** @var QueryBuilder $qb */
        $qb = $this->qb;

        return $qb
            ->andWhere('error.type = :type')
            ->setParameter('type', HttpError::TYPE_FILE)
            ->orderBy($order, $direction);
    }
}