<?php
namespace Victoire\Bundle\BlogBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * The Tag repository
 */
class TagRepository extends EntityRepository
{
    use StateFullRepositoryTrait;

    public function getAll()
    {
        $this->qb = $this->getInstance('t_tag')
                    ->join('t_tag.articles', 't_article')
        ;

        return $this;

    }
    public function filterByArticles($articles)
    {
        $this->qb
            ->andWhere($this->qb->expr()->in('t_article', $articles->getDql()))
            ->setParameters($articles->getParameters());
        return $this;
    }
}
