<?php
namespace Victoire\Bundle\PageBundle\Repository;

use Doctrine\ORM\Query;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Victoire\Bundle\PageBundle\Entity\PageStatus;

/**
 * The Page repository
 */
class BasePageRepository extends NestedTreeRepository
{
    private $queryBuilder;

    /**
     * Get query builder instance
     */
    public function getInstance()
    {
        return $this->queryBuilder ? $this->queryBuilder : $this->createQueryBuilder('page');
    }

    /**
     * Get the query builder for a page  by url
     *
     * @param string $url The url
     *
     * @return \Doctrine\ORM\QueryBuilder The query builder
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
        $queryBuilder = $this->getOneByUrl($url);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Filter the query by the sitemap index (=visibility)
     * @param bool $indexed
     *
     * @return BasePageRepository
     */
    public function filterBySitemapIndexed($indexed = true)
    {
        $qb = $this->getInstance();
        $qb->innerJoin('page.seo', 'seo')->addSelect('seo')
            ->andWhere('seo.sitemapIndexed = :sitemapIndexed')
            ->setParameter('sitemapIndexed', $indexed);

        return $this;
    }

    /**
     * Get all rentals in the repository.
     *
     * @param boolean $excludeUnpublished Should we get only the published BasePages ?
     *
     * @return BasePageRepository
     */
    public function getAll($excludeUnpublished = false)
    {
        $this->queryBuilder = $this->getInstance();

        //If $excludeUnpublished === true, we exclude the non published results
        if ($excludeUnpublished) {
            $this->queryBuilder
                ->andWhere('page.status = :status')
                ->orWhere('page.status = :scheduled_status AND page.publishedAt > :publicationDate')
                ->setParameter('status', PageStatus::PUBLISHED)
                ->setParameter('scheduled_status', PageStatus::SCHEDULED)
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

    /**
     * Get the the page that is a homepage and a published one
     * @param string $locale
     *
     * @return Page
     */
    public function findOneByHomepage($locale = 'fr')
    {
        //the query builder
        $queryBuilder = $this->createQueryBuilder('page');

        $queryBuilder
            ->where('page.homepage = true')
            ->andWhere('page.status = :status')
            ->andWhere('page.locale = :locale')
            ->setMaxResults(1)
            ->setParameter('locale', $locale)
            ->setParameter('status', PageStatus::PUBLISHED);

        $query = $queryBuilder->getQuery();
        $page = $query->getOneOrNullResult();

        return $page;
    }
}
