<?php

namespace Victoire\Bundle\CoreBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
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
    use StateFullRepositoryTrait;

    protected $mainAlias = 'view';

    /**
     * Get the query builder for a view  by url.
     *
     * @param string $url The url
     *
     * @return \Doctrine\ORM\QueryBuilder The query builder
     */
    public function getOneByUrl($url)
    {
        return $this->createQueryBuilder($this->mainAlias)
            ->where($this->mainAlias.'.url = (:url)')
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
        $qb->innerJoin($this->mainAlias.'.seo', 'seo')->addSelect('seo')
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
        $this->qb = $this->getInstance();

        //If $excludeUnpublished === true, we exclude the non published results
        if ($excludeUnpublished) {
            $this->qb
                ->andWhere($this->mainAlias.'.status = :status')
                ->orWhere($this->mainAlias.'.status = :scheduled_status AND '.$this->mainAlias.'.publishedAt > :publicationDate')
                ->setParameter('status', PageStatus::PUBLISHED)
                ->setParameter('scheduled_status', PageStatus::SCHEDULED)
                ->setParameter('publicationDate', new \DateTime());
        }

        return $this;
    }

    /**
     * Find a large amount of views by ViewReferences.
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

        $qb = $this->createQueryBuilder($this->mainAlias);
        $qb->andWhere($this->mainAlias.'.id IN (:pageIds)')
            ->setParameter('pageIds', $pageIds);

        $pages = $qb->getQuery()->getResult();

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
     * Get the the view that is a homepage and a published one.
     *
     * @param string $locale
     *
     * @return Page
     */
    public function findOneByHomepage($locale = 'fr')
    {
        //the query builder
        $qb = $this->createQueryBuilder($this->mainAlias);
        $qb
            ->where($this->mainAlias.'.homepage = true')
            ->andWhere($this->mainAlias.'.status = :status')
            ->setMaxResults(1)
            ->setParameter('status', PageStatus::PUBLISHED);
        // Use Translation Walker
        $query = $qb->getQuery();
        $view = $query->getOneOrNullResult();
        $view->translate($locale);

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
        $this->getInstance()->$method($this->mainAlias.'.seo', 'seo')->addSelect('seo');

        return $this;
    }

    /**
     * Get PageSeo.
     *
     * @param string $method leftJoin|innerJoin
     *
     * @return ViewRepository
     */
    public function joinTranslations($locale)
    {
        $this->getInstance()
            ->innerJoin($this->mainAlias.'.translations', 'translation', Expr\Join::WITH, 'translation.locale = :locale')
            ->setParameter('locale', $locale);

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
        $this->getInstance()->andWhere($this->mainAlias.'.id IN (:ids)')->setParameter('ids', $ids);

        return $this;
    }
}
