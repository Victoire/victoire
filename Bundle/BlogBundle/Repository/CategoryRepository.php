<?php
namespace Victoire\Bundle\BlogBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\CoreBundle\Repository\ChainedRepositoryTrait;

/**
 * The Category repository
 */
class CategoryRepository extends NestedTreeRepository
{
    use ChainedRepositoryTrait;

    public function getOrderedCategories(Blog $blog)
    {
        $this->qb = $this->getInstance('category')
                            ->join('category.blog', 'blog')
                            ->where('blog = :blog')
                            ->setParameter('blog', $blog)
        ;

        return $this;

    }

    public function getAll()
    {
        $this->qb = $this->getInstance('category')
        ;

        return $this;

    }


}
