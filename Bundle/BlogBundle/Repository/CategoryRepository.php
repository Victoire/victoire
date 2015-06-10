<?php
namespace Victoire\Bundle\BlogBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * The Category repository
 */
class CategoryRepository extends NestedTreeRepository
{
    use StateFullRepositoryTrait;

    public function getOrderedCategories(Blog $blog)
    {
        $this->qb = $this->getInstance('c_category')
                            ->join('c_category.blog', 'c_blog')
                            ->where('c_blog = :blog')
                            ->setParameter('blog', $blog)
        ;

        return $this;

    }
    public function getAll()
    {
        $this->qb = $this->getInstance('c_category')
                    ->join('c_category.articles', 'c_article')
        ;

        return $this;

    }
    public function filterByArticles($articles)
    {
        // var_dump($this->qb);die();
        $this->qb
            ->andWhere($this->qb->expr()->in('c_article', $articles->getDql()))
            ->setParameters($articles->getParameters());
        return $this;
    }


}
