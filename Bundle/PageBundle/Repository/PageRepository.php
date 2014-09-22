<?php
namespace Victoire\Bundle\PageBundle\Repository;

use Victoire\Bundle\PageBundle\Entity\PageStatus;

/**
 * The Page repository
 */
class PageRepository extends BasePageRepository
{

    /**
     * Get the the page that is a homepage and a published one
     *
     * @return Page
     */
    public function findOneByHomepage()
    {
        //the query builder
        $qb = $this->createQueryBuilder('page');

        $qb->where('page.homepage = true');
        $qb->andWhere('page.status = \''.PageStatus::PUBLISHED.'\'');
        $qb->setMaxResults(1);

        $query = $qb->getQuery();
        $page = $query->getOneOrNullResult();

        return $page;
    }
}
