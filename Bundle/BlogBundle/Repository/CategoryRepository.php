<?php
namespace Victoire\Bundle\BlogBundle\Repository;

use Victoire\Bundle\BlogBundle\Entity\Blog;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * The Category repository
 */
class CategoryRepository extends NestedTreeRepository
{
    use \AppVentus\Awesome\ShortcutsBundle\Repository\AwesomeRepositoryTrait;

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
