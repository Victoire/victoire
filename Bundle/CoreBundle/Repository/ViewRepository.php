<?php

namespace Victoire\Bundle\CoreBundle\Repository;

use Doctrine\ORM\Query;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Victoire\Bundle\PageBundle\Entity\PageStatus;

/**
 * The View repository.
 */
class ViewRepository extends NestedTreeRepository
{
    private $queryBuilder;

    /**
     * Get query builder instance.
     */
    public function getInstance()
    {
        return $this->queryBuilder ? $this->queryBuilder : $this->createQueryBuilder('page');
    }

    /**
     * Get the query builder for a view  by url.
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
     * Filter the query by the sitemap index (=visibility).
     *
     * @param bool $indexed
     *
     * @return ViewRepository
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
     * @param bool $excludeUnpublished Should we get only the published Views ?
     *
     * @return ViewRepository
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
     * Run instance.
     *
     * @param string $method
     * @param string $hydrationMode
     *
     * @return array
     */
    public function run($method = 'getResult', $hydrationMode = Query::HYDRATE_OBJECT)
    {
        return $this->getInstance()->getQuery()->$method($hydrationMode);
    }

    /**
     * Get the the view that is a homepage and a published one.
     *
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
        $view = $query->getOneOrNullResult();

        return $view;
    }
}
