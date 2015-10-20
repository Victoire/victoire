<?php

namespace Victoire\Bundle\AnalyticsBundle\Helper;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Victoire\Bundle\PageBundle\Helper\PageHelper;

/**
 * Analytics View helper
 * ref: victoire_analytics.view_helper.
 */
class AnalyticsViewHelper
{
    protected $viewCacheHelper;
    protected $entityManager;
    protected $pageHelper;

    public function __construct(ViewCacheHelper $viewCacheHelper, EntityManager $entityManager, PageHelper $pageHelper)
    {
        $this->entityManager = $entityManager;
        $this->viewCacheHelper = $viewCacheHelper;
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
                    $viewReference = $this->viewCacheHelper->getReferenceByParameters(
                        [
                            'viewNamespace' => $viewNamespace,
                            'viewId'        => $page->getId(),
                        ]
                    );
                    $viewReferences[$viewReference['id']] = $viewReference;
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
                # code...
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
            $viewReference = $this->viewCacheHelper->getReferenceByParameters(
                [
                    'entityNamespace' => 'Victoire\Bundle\BlogBundle\Entity\Article',
                    'entityId'        => $article->getId(),
                ]
            );
            $viewReferences[$viewReference['id']] = $viewReference;
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
