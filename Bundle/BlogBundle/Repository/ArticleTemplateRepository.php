<?php

namespace Victoire\Bundle\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * The Article Template repository.
 */
class ArticleTemplateRepository extends EntityRepository {

    use StateFullRepositoryTrait;

    /**
     * @param $blog_id
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCount($alias = 'at') {
        $this->qb->select("count($alias.id)");

        return $this;
    }

    /**
     * @param $blog_id
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function filterByBlog($blog_id) {
        $this->clearInstance();
        $this->qb = $this->getInstance('at');

        $this->qb
            ->join('at.parent', 'parent')
            ->andWhere('parent.id = :id')
            ->setParameter('id', $blog_id);

        return $this;
    }

}
