<?php

namespace Victoire\Bundle\PageBundle\Twig\Extension;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\BusinessEntityPageBundle\Helper\BusinessEntityPageHelper;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Helper\PageHelper;

/**
 * Page extension
 */
class PageExtension extends \Twig_Extension
{
    protected $businessEntityPagePatternHelper = null;
    protected $router = null;
    protected $pageHelper = null;

    /**
     * Constructor
     *
     * @param BusinessEntityPageHelper $businessEntityPagePatternHelper
     * @param Router $router
     * @param PageHelper $pageHelper
     * @param CurrentViewHelper $currentViewHelper
     */
    public function __construct(BusinessEntityPageHelper $businessEntityPagePatternHelper, Router $router, PageHelper $pageHelper, CurrentViewHelper $currentViewHelper)
    {
        $this->businessEntityPagePatternHelper = $businessEntityPagePatternHelper;
        $this->router = $router;
        $this->pageHelper = $pageHelper;
        $this->currentViewHelper = $currentViewHelper;
    }

    /**
     * register twig functions
     *
     * @return array The list of extensions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('vic_current_page_reference', array($this, 'victoireCurrentPageReference')),
            'cms_page_business_page_pattern_sitemap' => new \Twig_Function_Method($this, 'cmsPageBusinessPagePatternSiteMap', array('is_safe' => array('html'))),
        );
    }

    /**
     * register twig filters
     *
     * @return array The list of filters
     */
    public function getFilters()
    {
        return array();
    }

    /**
     * get extension name
     *
     * @return string The name
     */
    public function getName()
    {
        return 'cms_page';
    }

    /**
     * Get the ol li for the generated page of a business entity page pattern
     *
     * @param BasePage $page
     *
     * @return string The html
     */
    public function cmsPageBusinessPagePatternSiteMap(BasePage $page)
    {
        $html = '';

        $urls = array();

        //the template link to the page
        $businessEntityPagePattern = $page;

        //
        if ($page instanceof BusinessEntityPagePattern) {
            //get the list of url of the children to avoid to have it twice.
            $childrenUrls = $this->getChildrenUrls($page);

            //services
            $businessEntityPagePatternHelper = $this->businessEntityPagePatternHelper;
            $pageHelper = $this->pageHelper;

            //the items allowed for the template
            $items = $businessEntityPagePatternHelper->getEntitiesAllowed($businessEntityPagePattern);

            //parse entities
            foreach ($items as $item) {
                $pageEntity = clone $businessEntityPagePattern;

                //update url using the entity instance
                $pageHelper->updatePageParametersByEntity($pageEntity, $item);

                $url = $pageEntity->getUrl();

                //if the url does no exists in the children
                if (!in_array($url, $childrenUrls)) {
                    $generated = true;
                } else {
                    $generated = false;
                }

                //update the parameters of the page
                $pageHelper->updatePageParametersByEntity($pageEntity, $item);

                $title = $pageEntity->getName();

                $itemsToAdd[$url] = array(
                    'item'      => $item,
                    'url'       => $url,
                    'title'     => $title,
                    'generated' => $generated,
                );

                unset($url);
            }

            //render the ol li
            $html .= '<ol>';
            foreach ($itemsToAdd as $item) {
                $itemUrl = $item['url'];
                $itemUrl = $this->router->generate('victoire_core_page_show', array('url' => $itemUrl));
                $title = $item['title'];
                $generated = $item['generated'];

                //the class to identify the generated pages
                if ($generated) {
                    $class = 'generated';
                } else {
                    $class = '';
                }

                $html .= "<li><div class='".$class."'><a href='".$itemUrl."' title='".$title."'>".$title."</a></div>";
            }
            $html .= '</ol>';
        }

        return $html;
    }

    /**
     * Get the list of urls of the children
     *
     * @param BasePage $page
     *
     * @return aray of strings The list of urls
     */
    protected function getChildrenUrls(BasePage $page)
    {
        $urls = array();

        $children = $page->getInstances();

        //parse the children
        foreach ($children as $child) {
            $url = $child->getUrl();
            $urls[] = $url;

            unset($url);
        }

        return $urls;
    }

    public function victoireCurrentPageReference()
    {
        $currentView = $this->currentViewHelper;

        return $currentView->getMainCurrentView()->getReference()['id'];
    }
}
