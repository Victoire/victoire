<?php
namespace Victoire\Bundle\BlogBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\BlogBundle\Entity\Article;

use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * The Article repository
 */
class ArticleRepository extends EntityRepository
{
    use StateFullRepositoryTrait;

    /**
     * Get all articles in the repository.
     *
     * @param boolean $excludeUnpublished Should we get only the published BasePages ?
     *
     * @return ArticleRepository
     */
    public function getAll($excludeUnpublished = false)
    {
        $this->clearInstance();
        $this->qb = $this->getInstance();

        //If $excludeUnpublished === true, we exclude the non published results
        if ($excludeUnpublished) {
            $this->qb
                ->andWhere('article.status = :status')
                ->orWhere('article.status = :scheduled_status AND article.publishedAt > :publicationDate')
                ->setParameter('status', Article::PUBLISHED)
                ->setParameter('scheduled_status', Article::SCHEDULED)
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

    public function filterWithListingQuery($listingQuery = null)
    {
        if ($listingQuery) {
            $dql = $this->createQueryBuilder('a_article')
                ->leftJoin('a_article.blog', 'blog')
                ->getDql()
            ;
            $dql = $dql.' '.$listingQuery;
            $this->qb
                ->andWhere($this->qb->expr()->in('article', $dql));
        }
        return $this;
    }
}
