<?php

namespace Victoire\Bundle\AnalyticsBundle\Helper;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;

/**
 * Analytics View helper
 * ref: victoire_analytics.view_helper.
 */
class AnalyticsViewHelper
{
    protected $viewReferenceRepository;
    protected $entityManager;
    protected $pageHelper;

    /**
     * AnalyticsViewHelper constructor.
     *
     * @param ViewReferenceRepository $viewReferenceRepository
     * @param EntityManager           $entityManager
     * @param PageHelper              $pageHelper
     */
    public function __construct(ViewReferenceRepository $viewReferenceRepository, EntityManager $entityManager, PageHelper $pageHelper)
    {
        $this->entityManager = $entityManager;
        $this->viewReferenceRepository = $viewReferenceRepository;
        $this->pageHelper = $pageHelper;
    }

    /**
     * Get the most read views by type.
     *
     * @return View[]
     **/
    public function getMostReadByViewType($viewNamespace, $number)
    {
        $views = [];

        switch ($viewNamespace) {
            case 'Victoire\Bundle\PageBundle\Entity\Page':

                $viewReferences = [];
                $repo = $this->entityManager->getRepository($viewNamespace);
                //get pages and viewReferenceIds
                foreach ($repo->getAll()->run() as $key => $page) {
                    $viewReference = $this->viewReferenceRepository->getOneReferenceByParameters(
                        [
                            'viewNamespace' => $viewNamespace,
                            'viewId'        => $page->getId(),
                        ]
                    );
                    $viewReferences[$viewReference->getId()] = $viewReference;
                }
                //get pager
                $browseEvents = $this->entityManager->getRepository('Victoire\Bundle\AnalyticsBundle\Entity\BrowseEvent')
                    ->getMostVisitedFromReferences(array_keys($viewReferences), $number)
                    ->getQuery()
                    ->getResult();
                //Now we get the most visited references, we'll get views with PageHelper
                foreach ($browseEvents as $browseEvent) {
                    $views[] = $this->pageHelper->findPageByReference(
                        $viewReferences[$browseEvent->getViewReferenceId()]
                    );
                }

                break;

            default:
                // code...
                break;
        }

        return $views;
    }

    /**
     * Get the most read articles by blog.
     *
     * @return Article[]
     **/
    public function getMostReadArticlesByBlog($blog, $number, $excludeUnpublished = true)
    {
        $viewReferences = [];
        //get articles and viewReferenceIds
        $articles = $this->entityManager->getRepository('Victoire\Bundle\BlogBundle\Entity\Article')
                    ->getAll($excludeUnpublished)
                    ->filterByBlog($blog)
                    ->run();

        foreach ($articles as $key => $article) {
            if ($viewReference = $this->viewReferenceRepository->getOneReferenceByParameters(
                [
                    'entityNamespace' => 'Victoire\Bundle\BlogBundle\Entity\Article',
                    'entityId'        => $article->getId(),
                ]
            )) {
                $viewReferences[$viewReference->getId()] = $viewReference;
            }
        }
        //get pager
        $browseEvents = $this->entityManager->getRepository('Victoire\Bundle\AnalyticsBundle\Entity\BrowseEvent')
            ->getMostVisitedFromReferences(array_keys($viewReferences), $number)
            ->getQuery()
            ->getResult();

        $views = [];
        //Now we get the most visited references, we'll get views with PageHelper
        foreach ($browseEvents as $browseEvent) {
            $views[] = $this->pageHelper->findPageByReference(
                $viewReferences[$browseEvent->getViewReferenceId()]
            );
        }

        return $views;
    }

    /**
     * Get number of unique visitor for a viewReference.
     *
     * @param string $viewReferenceId
     */
    public function getVisitorCountForViewReference($viewReferenceId)
    {
        $viewCount = $this->entityManager->getRepository('Victoire\Bundle\AnalyticsBundle\Entity\BrowseEvent')
            ->getNumberOfEventForViewReferenceId($viewReferenceId)
            ->getQuery()
            ->getSingleScalarResult();

        return $viewCount;
    }
}
