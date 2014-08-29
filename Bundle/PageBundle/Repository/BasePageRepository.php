<?php
namespace Victoire\Bundle\PageBundle\Repository;

use Doctrine\ORM\Query;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * The Page repository
 */
class BasePageRepository extends NestedTreeRepository
{

    private $qb;

    /**
     * Get query builder instance
     */
    public function getInstance()
    {
        return $this->qb ? $this->qb : $this->createQueryBuilder('page');
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

    /**
     * Get all rentals in the repository.
     *
     * @param boolean $excludeUnpublished Should we get only the published BasePages ?
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAll($excludeUnpublished = false)
    {
        $this->qb = $this->getInstance();

        //If $excludeUnpublished === true, we exclude the non published results
        if ($excludeUnpublished) {
            $this->qb
                ->andWhere('page.status = :status')
                ->orWhere('page.status = :scheduled_status AND page.publishedAt > :publicationDate')
                ->setParameter('status', BasePage::$statusPublished)
                ->setParameter('scheduled_status', BasePage::$statusScheduled)
                ->setParameter('publicationDate', new \DateTime());
        }

        return $this;
    }

    /**
     * Get very next festivals query builder
     * @param method        $method        The method to run
     * @param hydrationMode $hydrationMode How the results will be (Object ? Array )
     *
     * @return array()
     */
    public function run($method = 'getResult', $hydrationMode = Query::HYDRATE_OBJECT)
    {
        return $this->getInstance()->getQuery()->$method($hydrationMode);
    }
}
