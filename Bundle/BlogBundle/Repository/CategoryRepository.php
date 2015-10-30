<?php

namespace Victoire\Bundle\BlogBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * The Category repository.
 */
class CategoryRepository extends NestedTreeRepository
{
    use StateFullRepositoryTrait;

    /**
     * @param $blogId
     *
     * @return $this
     */
    public function getOrderedCategories($blogId)
    {
        $this->qb = $this->getInstance('c_category')
            ->leftJoin('c_category.blog', 'c_blog')
            ->where('c_blog.id = :blogId')
            ->setParameter('blogId', $blogId);

        return $this;
    }

    /**
     * @return $this
     */
    public function getAll()
    {
        $this->qb = $this->getInstance('c_category')
            ->leftJoin('c_category.articles', 'c_article');

        return $this;
    }

    /**
     * Order categories by tree hierarchy.
     *
     * @return $this
     */
    public function orderByHierarchy()
    {
        $this->qb
            ->addOrderBy('c_category.root')
            ->addOrderBy('c_category.lft')
            ->addOrderBy('c_category.lvl');

        return $this;
    }

    /**
     * @param $articles
     *
     * @return $this
     */
    public function filterByArticles($articles)
    {
        $this->qb
            ->andWhere($this->qb->expr()->in('c_article', $articles->getDql()))
            ->setParameters($articles->getParameters());

        return $this;
    }
}
