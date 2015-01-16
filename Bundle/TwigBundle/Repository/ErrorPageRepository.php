<?php

namespace Victoire\Bundle\TwigBundle\Repository;

use Victoire\Bundle\PageBundle\Repository\BasePageRepository;

/**
 * The Page repository
 */
class ErrorPageRepository extends BasePageRepository
{

    /**
     * Get a page according to the given code
     * @param integer $code The error code
     *
     * @return Page
     */
    public function findOneByCode($code, $deepMode = false)
    {
       //the query builder
        $page = $this->createQueryBuilder('page')
            ->where('page.code = :code')
            ->orWhere('page.code = :baseCode')
            ->setParameter('code', $code)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$page && $deepMode) {

            // Check for a same family error
            // for example, for a 404 code, if the 404 error page doesn't exist, we check for a 400 errorPage
            $page = $this->findOneByCode(floor($code/100)*100);
        }

        return $page;
    }
}
