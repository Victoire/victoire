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
 * ref: victoire_core.url_builder
 */
class UrlBuilder
{
    
    /**
     * Builds the page's url by get all page parents slugs and implode them with "/".
     * @param WebViewInterface $view
     * @param integer $depth
     *
     * @return string $url
     */
    public function buildUrl(WebViewInterface $view)
    {
        $slug = [];
        // build url binded with parents url
        if (method_exists($view, 'getStaticUrl') && $view->getStaticUrl() != null && $view->getStaticUrl() != '' ) {
            $slug = array($view->getStaticUrl());
        } else if (!(method_exists($view, 'isHomepage') && $view->isHomepage())) {
            $slug = array($view->getSlug());
        }

        //get the slug of the parents
        $url = $this->getParentSlugs($view, $slug);

        //reorder the list of slugs
        $url = array_reverse($url);
        //build an url based on the slugs
        $url = implode('/', $url);


        return $url;

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
            if (method_exists($parent, 'getStaticUrl') && $parent->getStaticUrl() != null && $parent->getStaticUrl() != '' ) {
                array_push($slugs, $parent->getStaticUrl());
            } else if (!(method_exists($parent, 'isHomepage') && $parent->isHomepage())) {
                array_push($slugs, $parent->getSlug());
            }

            if ($parent->getParent() !== null) {
                $slugs = array_merge($slugs, $this->getParentSlugs($parent, $slugs));
            }
        }

        return array_unique($slugs);
    }


}
