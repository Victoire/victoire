<?php

namespace Victoire\Bundle\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * The Tag repository.
 */
class TagRepository extends EntityRepository
{
    use StateFullRepositoryTrait;

    public function getAll()
    {
        $this->qb = $this->getInstance('t_tag')
                    ->join('t_tag.articles', 't_article');

        return $this;
    }

    public function filterByArticles($articles)
    {
        $this->qb
            ->andWhere($this->qb->expr()->in('t_article', $articles->getDql()))
            ->setParameters($articles->getParameters());

        return $this;
    }

    public function filterByBlog(Blog $blog = null)
    {
        $this->qb = $this->getInstance('t_tag')
            ->leftJoin('t_tag.blog', 'blog');
        if ($blog) {
            $this->qb = $this->qb->where('blog.id = :blogId')
                ->orWhere('blog.id IS NULL')
            ->setParameters(['blogId' => $blog->getId()]);
        }

        return $this;
    }
}
