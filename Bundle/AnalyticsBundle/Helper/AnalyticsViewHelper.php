<?php

namespace Victoire\Bundle\AnalyticsBundle\Helper;

use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Doctrine\ORM\EntityManager;

/**
 * Analytics View helper
 * ref: victoire_analytics.view_helper
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
     * Get the most read views by type
     *
     * @return View[]
     **/
    public function getMostReadByViewType($viewNamespace)
    {
        throw new \Exception("Implement me");
    }

    /**
     * Get the most read articles by blog
     *
     * @return Article[]
     **/
    public function getMostReadArticlesByBlog($blog, $number)
    {
        $viewReferences = array();
        //get articles and viewReferenceIds
        foreach ($blog->getArticles() as $key => $article) {
            $viewReference = $this->viewCacheHelper->getReferenceByParameters(
                array(
                    'entityNamespace' => 'Victoire\Bundle\BlogBundle\Entity\Article',
                    'entityId' => $article->getId(),
                )
            );
            $viewReferences[$viewReference['id']] = $viewReference;
        }
        //get pager
        $browseEvents = $this->entityManager->getRepository('Victoire\Bundle\AnalyticsBundle\Entity\BrowseEvent')
            ->getMostVisited(array_keys($viewReferences), $number)
            ->getQuery()
            ->getResult();

        $views = array();
        //Now we get the most visited references, we'll get views with PageHelper
        foreach ($browseEvents as $browseEvent) {
            $views[] = $this->pageHelper->findPageByReference(
                $viewReferences[$browseEvent->getViewReferenceId()]
            );
        }

        return $views;
    }
}
