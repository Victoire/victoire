<?php

namespace Victoire\Bundle\BlogBundle\Manager;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;

/**
 * article Manager
 * ref. victoire_blog.manager.article
 */
class ArticleManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * Delete a given article
     * @param Article            $article
     */
    public function delete(Article $article, BusinessEntityPage $bep)
    {
        $this->entityManager->remove($bep);

        $article->setVisibleOnFront(0);
        $this->entityManager->flush();

        $this->entityManager->remove($article);
        $this->entityManager->flush();
    }
}
