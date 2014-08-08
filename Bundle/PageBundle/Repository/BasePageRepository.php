<?php
namespace Victoire\Bundle\PageBundle\Repository;

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
        $qb = $this->getInstance();

        //If $excludeUnpublished === true, we exclude the non published results
        if ($excludeUnpublished) {
            $qb
                ->andWhere('page.status = :status')
                ->orWhere('page.status = :scheduled_status AND page.publishedAt > :publicationDate')
                ->setParameter('status', BasePage::STATUS_PUBLISHED)
                ->setParameter('scheduled_status', BasePage::STATUS_SCHEDULED)
                ->setParameter('publicationDate', new \DateTime());
        }

        return $qb;
    }
}
