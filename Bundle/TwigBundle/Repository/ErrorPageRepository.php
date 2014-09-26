<?php

namespace Victoire\Bundle\TwigBundle\Repository;

use Victoire\Bundle\PageBundle\Repository\BasePageRepository;

/**
 * The Page repository
 */
class ErrorPageRepository extends BasePageRepository
{

    /**
     * Get the the page that is a homepage and a published one
     *
     * @return Page
     */
    public function findOneByCode($code)
    {
       //the query builder
        $page = $this->createQueryBuilder('page')
            ->where('page.code = :code')
            ->orWhere('page.code = :baseCode')
            ->setParameter('code', $code)
            ->setParameter('baseCode', floor($code/100)*100)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $page;
    }
}
