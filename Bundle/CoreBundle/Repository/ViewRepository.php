<?php

namespace Victoire\Bundle\CoreBundle\Repository;

use Doctrine\ORM\Query;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Entity\PageStatus;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\BusinessPageReference;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

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
     * Find a large amount of views by ViewReferences and optimizing queries with translation walker.
     *
     * @param ViewReference[] $viewReferences
     *
     * @return View[]|null The entity instance or NULL if the entities cannot be found.
     */
    public function findByViewReferences(array $viewReferences)
    {
        $pageIds = [];
        foreach ($viewReferences as $viewReference) {
            if ($viewReference instanceof BusinessPageReference) {
                $pageIds[] = $viewReference->getTemplateId();
            } else {
                $pageIds[] = $viewReference->getViewId();
            }
        }


        $queryBuilder = $this->createQueryBuilder('page');
        $queryBuilder->andWhere('page.id IN (:pageIds)')
            ->setParameter('pageIds', $pageIds);

        $pages = $queryBuilder->getQuery()->getResult();

        foreach ($pages as $page) {
            $pageId = $page->getId();
            $viewReference = array_filter(
                $viewReferences,
                function ($e) use ($pageId) {
                    return $e->getViewId() == $pageId;
                });
            if (!empty($viewReference[0])) {
                $page->setCurrentLocale($viewReference[0]->getLocale());
            }
        }

        return $pages;
    }

    /**
     * Finds a single entity by a set of criteria.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);

        $hints = [];
        if (isset($criteria['locale'])) {
            $hints = [
                \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER                     => 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker',
                \Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE => $criteria['locale'],
            ];
            unset($criteria['locale']);
        }

        return $persister->load($criteria, null, null, $hints, null, 1, $orderBy);
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
            ->setMaxResults(1)
            ->setParameter('status', PageStatus::PUBLISHED);

        // Use Translation Walker
        $query = $queryBuilder->getQuery();
        $query->setHint(
            \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );
        // Force the locale
        $query->setHint(
            \Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE,
            $locale
        );

        $view = $query->getOneOrNullResult();

        return $view;
    }

    /**
     * Get PageSeo.
     *
     * @param string $method leftJoin|innerJoin
     *
     * @return ViewRepository
     */
    public function joinSeo($method = 'leftJoin')
    {
        $this->getInstance()->$method('page.seo', 'seo')->addSelect('seo');

        return $this;
    }

    /**
     * Filter the query by the sitemap index (=visibility).
     *
     * @param array $ids
     *
     * @return ViewRepository
     */
    public function filterByIds($ids)
    {
        $this->getInstance()->andWhere('page.id IN (:ids)')->setParameter('ids', $ids);

        return $this;
    }
}
