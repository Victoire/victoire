<?php

namespace Victoire\Bundle\BlogBundle\Manager;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;

/**
 * article Manager
 * ref. victoire_blog.manager.article.
 */
class ArticleManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Delete a given article.
     *
     * @param Article $article
     */
    public function delete(Article $article, BusinessPage $bep)
    {
        $this->entityManager->remove($bep);
        $article->setVisibleOnFront(0);
        $article->setDeletedAt(new \DateTime());

        //flush the modifications
        $this->entityManager->flush();
    }
}
