<?php
namespace Victoire\Bundle\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 *
 * @author Thomas Beaujean
 *
 */
class RouteRepository extends EntityRepository
{
    /**
     * Get the most recent route by url
     *
     * @param string $url The url to search
     * @return Route
     */
    public function findOneMostRecentByUrl($url)
    {
        $entity = null;

        $qb = $this->createQueryBuilder('route');
        $qb->where('route.url = :url');
        $qb->setParameter(':url', $url);
        $qb->orderBy('route.id', 'DESC');
        $qb->setMaxResults(1);

        $results = $qb->getQuery()->getResult();

        if (count($results) > 0) {
            $entity = $results[0];
        }

        return $entity;
    }
}
