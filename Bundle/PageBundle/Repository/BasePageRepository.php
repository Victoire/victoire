<?php
namespace Victoire\Bundle\PageBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * The basePage repository
 *
 * @author Thomas Beaujean thomas@appventus.com
 *
 */
class BasePageRepository extends NestedTreeRepository
{

    /**
     * Get the the page that is a homepage and a published one
     *
     * @return BasePage
     */
    public function findOneByHomepage()
    {
        //the query builder
        $qb = $this->createQueryBuilder('page');

        $qb->where('page.homepage = true');
        $qb->addWhere('page.status = \''.BasePage::STATUS_PUBLISHED.'\'');
        $qb->setMaxResults(1);

        $query = $qb->getQuery();
        $page = $query->getOneOrNullResult();


        return $page;
    }

    /**
     * Get the query builder for a page  by url
     *
     * @param string $url The url
     *
     * @return QueryBuilder The query builder
     */
    public function getOneByUrl($url)
    {
        return $this->createQueryBuilder('page')
            ->where('page.url = (:url)')
            ->setMaxResults(1)
            ->setParameter('url', $url);
    }

    /**
     * Get the page by the url
     *
     * @param string $url
     *
     * @return Page
     */
    public function findOneByUrl($url)
    {
        $qb = $this->getOneByUrl($url);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
