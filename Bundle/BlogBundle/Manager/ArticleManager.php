<?php

namespace Victoire\Bundle\BlogBundle\Manager;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\BlogBundle\Entity\Tag;
use Victoire\Bundle\BusinessPageBundle\Builder\BusinessPageBuilder;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Transformer\VirtualToBusinessPageTransformer;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\PageStatus;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Victoire\Bundle\UserBundle\Entity\User;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;
use Victoire\Bundle\ViewReferenceBundle\Exception\ViewReferenceNotFoundException;

/**
 * Article Manager
 *
 * ref. victoire_blog.manager.article.
 */
class ArticleManager
{
    private $entityManager;
    private $businessPageBuilder;
    private $virtualToBusinessPageTransformer;
    private $pageHelper;
    private $viewReferenceRepo;

    public function __construct(
        EntityManager $entityManager,
        BusinessPageBuilder $businessPageBuilder,
        VirtualToBusinessPageTransformer $virtualToBusinessPageTransformer,
        PageHelper $pageHelper,
        ViewReferenceRepository $viewReferenceRepo
    )
    {
        $this->entityManager = $entityManager;
        $this->businessPageBuilder = $businessPageBuilder;
        $this->virtualToBusinessPageTransformer = $virtualToBusinessPageTransformer;
        $this->pageHelper = $pageHelper;
        $this->viewReferenceRepo = $viewReferenceRepo;
    }

    /**
     * Create Article with its author, tags.
     * Create BusinessPage for this Article.
     *
     * @param Article $article
     * @param User $author
     *
     * @return BusinessPage
     */
    public function create(Article $article, User $author)
    {
        $article->setAuthor($author);

        /** @var Tag[] $tags */
        $tags = $article->getTags();
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $tag->setBlog($article->getBlog());
                $this->entityManager->persist($tag);
            }
        }

        //Article has to be persisted before BusinessPage generation
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $page = $this->businessPageBuilder->generateEntityPageFromTemplate(
            $article->getTemplate(),
            $article,
            $this->entityManager
        );

        //Transform VBP into BP
        $this->virtualToBusinessPageTransformer->transform($page);
        $page->setParent($article->getBlog());

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }

    /**
     * Update Blog Article settings.
     * 
     * @param Article $article
     *
     * @return View
     *
     * @throws ViewReferenceNotFoundException
     */
    public function updateSettings(Article $article)
    {
        //Update Tags
        /** @var Tag[] $tags */
        $tags = $article->getTags();
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $tag->setBlog($article->getBlog());
                $this->entityManager->persist($tag);
            }
        }

        //Update BusinessPage
        $businessPage = $this->pageHelper->findPageByParameters([
            'viewId'   => $article->getTemplate()->getId(),
            'entityId' => $article->getId(),
        ]);
        $template = $article->getTemplate();
        $businessPage->setTemplate($template);

        //Update Page
        $page = $this->pageHelper->findPageByParameters([
            'viewId'   => $template->getId(),
            'entityId' => $article->getId(),
        ]);
        $page->setName($article->getName());
        $page->setSlug($article->getSlug());
        $page->setStatus($article->getStatus());

        $this->entityManager->flush();

        //Set ViewReference for Page redirection
        $viewReference = $this->viewReferenceRepo->getOneReferenceByParameters(
            ['viewId' => $page->getId()]
        );
        $page->setReference($viewReference);

        return $page;
    }

    /**
     * Delete a given Article.
     *
     * @param Article $article
     */
    public function delete(Article $article)
    {
        $bep = $this->pageHelper->findPageByParameters(
            [
                'templateId' => $article->getTemplate()->getId(),
                'entityId' => $article->getId(),
            ]
        );
        $this->entityManager->remove($bep);

        $article->setVisibleOnFront(0);
        $article->setDeletedAt(new \DateTime());
        $article->setStatus(PageStatus::DELETED);

        $this->entityManager->flush();
    }
}
