<?php
namespace Victoire\Bundle\CoreBundle\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Victoire\Bundle\CoreBundle\Entity\Route;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\PageBundle\Repository\PageRepository;

/**
 * ref: victoire_core.view_url_helper
 */
class ViewUrlHelper
{
    protected $request = null;
    protected $router = null;

    /**
     * Constructor
     * @param Router         $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Set the current request
     *
     * @param RequestStack $requestStack
     */
    public function setRequest(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Is this url is already used
     * @param string        $url     The url to test
     * @param EntityManager $entityManager
     * @param integer       $suffix The suffix
     *
     * @return string The next available url
     */
    public function getNextAvailaibleUrl($url, EntityManager $entityManager, $suffix = 1)
    {
        $pageRepo = $entityManager->getRepository('VictoirePageBundle:Page');
        $isUrlAlreadyUsed = (bool) $pageRepo->findOneByUrl($url);

        //if the url is alreay used, we look for another one
        if ($isUrlAlreadyUsed) {
            $urlWithSuffix = $url.'-'.$suffix;

            //try to get a page with this url
            $isUrlAlreadyUsed = (bool) $pageRepo->findOneByUrl($urlWithSuffix);


            //the url is still used, we try the next one
            if ($isUrlAlreadyUsed) {
                //get the next available url
                $url = $this->getNextAvailaibleUrl($url, $entityManager, $suffix + 1);
            } else {
                //this url if free, let us use it
                $url = $urlWithSuffix;
            }
        }

        return $url;
    }

    /**
     * Remove the last part of the url
     * @param string $url
     *
     * @return string The shorten url
     */
    public function removeLastPart($url)
    {
        $shortenUrl = null;

        if ($url !== null && $url !== '') {
            // split on the / character
            $keywords = preg_split("/\//", $url);

            //if there are some words, we pop the last
            if (count($keywords) > 0) {
                array_pop($keywords);

                //rebuild the url
                $shortenUrl = implode('/', $keywords);
            }
        }

        return $shortenUrl;
    }

    /**
     * Extract a part of the url
     * @param string  $url
     * @param integer $position
     *
     * @return string The extracted part
     */
    public function extractPartByPosition($url, $position)
    {
        $part = null;

        if ($url !== null && $url !== '') {
            // split on the / character
            $keywords = preg_split("/\//", $url);
            // preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $url, $matches);

            //if there are some words, we pop the last
            if (count($keywords) > 0) {
                //get the part
                $part = $keywords[$position - 1];
            }
        }

        return $part;
    }

    /**
     * Builds the page's url by get all page parents slugs and implode them with "/".
     * Builds the pages children urls with new page slug
     * If page has a custom url, we don't modify it, but we modify children urls
     * @param WebViewInterface $view
     * @param integer $depth
     *
     * @return void
     */
    public function buildUrl(WebViewInterface $view, UnitOfWork $uow, EntityManager $entityManager, $depth = 0)
    {
        $initialUrl = $view->getUrl();
        // build url binded with parents url
        if (method_exists($view, 'isHomepage') && $view->isHomepage()) {
            $url = array('');
        } else if (method_exists($view, 'getStaticUrl') && $view->getStaticUrl() != null && $view->getStaticUrl() != '' ) {
            $url = array($view->getStaticUrl());
        } else {
            $url = array($view->getSlug());
        }

        //get the slug of the parents
        $url = $this->getParentSlugs($view, $url);

        //reorder the list of slugs
        $url = array_reverse($url);
        //build an url based on the slugs
        $url = implode('/', $url);

        //get the next free url
        $url = $this->getNextAvailaibleUrl($url, $entityManager);

        //update url of the view
        $view->setUrl($url);

        //the metadata of the page
        $meta = $entityManager->getClassMetadata(get_class($view));

        if ($depth === 0) {
            $uow->recomputeSingleEntityChangeSet($meta, $view);
        } else {
            $uow->computeChangeSet($meta, $view);
        }

        $this->rebuildChildrenUrl($view, $uow, $entityManager, $depth);
        if ($view->getId()) {
            $this->addRouteHistory($view, $initialUrl, $uow, $entityManager);
        }
    }

    /**
     * @param WebViewInterface $view
     * @param integer       $depth
     * @param UnitOfWork    $uow
     * @param EntityManager $entityManager
     */
    protected function rebuildChildrenUrl(WebViewInterface $view, UnitOfWork $uow, EntityManager $entityManager, $depth = 0)
    {
        $children = $view->getChildren();

        $depth++;
        if ($children) {
            foreach ($children as $child) {
                // recursive call for each children
                $this->buildUrl($child, $uow, $entityManager, $depth);
                $meta = $entityManager->getClassMetadata(get_class($child));
                $uow->computeChangeSet($meta, $child);
            }
        }
    }

    /**
     * Get the array of slugs of the parents
     * @param WebViewInterface $view
     * @param array            $slugs
     *
     * @return array
     */
    protected function getParentSlugs(WebViewInterface $view, array $slugs)
    {
        $parent = $view->getParent();

        if ($parent !== null) {
            array_push($slugs, $parent->getSlug());
            if ($parent->getParent() !== null) {
                $slugs = array_merge($slugs, $this->getParentSlugs($parent, $slugs));
            }
        }

        return array_unique($slugs);
    }

    /**
     * Record the route history of the page
     *
     * @param WebViewInterface $view
     * @param string           $initialUrl
     */
    protected function addRouteHistory(WebViewInterface $view, $initialUrl, UnitOfWork $uow, EntityManager $entityManager)
    {
        $route = new Route();
        $route->setUrl($initialUrl);
        $route->setView($view);
        $meta = $entityManager->getClassMetadata(get_class($route));
        $entityManager->persist($route);
        $uow->computeChangeSet($meta, $route);

        //add the route to the page
        $view->addRoute($route);
    }
}
