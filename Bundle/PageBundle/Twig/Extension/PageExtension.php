<?php

namespace Victoire\Bundle\PageBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Helper\PageHelper;

/**
 * Page extension.
 */
class PageExtension extends \Twig_Extension
{
    protected $BusinessTemplateHelper = null;
    protected $router = null;
    protected $pageHelper = null;

    /**
     * Constructor.
     *
     * @param BusinessPageHelper $BusinessTemplateHelper
     * @param Router             $router
     * @param PageHelper         $pageHelper
     * @param CurrentViewHelper  $currentViewHelper
     * @param EntityManager      $entityManager
     */
    public function __construct(BusinessPageHelper $BusinessTemplateHelper, Router $router, PageHelper $pageHelper, CurrentViewHelper $currentViewHelper, EntityManager $entityManager)
    {
        $this->BusinessTemplateHelper = $BusinessTemplateHelper;
        $this->router = $router;
        $this->pageHelper = $pageHelper;
        $this->currentViewHelper = $currentViewHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * register twig functions.
     *
     * @return array The list of extensions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('vic_current_page_reference', [$this, 'victoireCurrentPageReference']),
            'cms_page_business_page_pattern_sitemap' => new \Twig_Function_Method($this, 'cmsPageBusinessPagePatternSiteMap', ['is_safe' => ['html']]),
        ];
    }

    /**
     * register twig filters.
     *
     * @return array The list of filters
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * get extension name.
     *
     * @return string The name
     */
    public function getName()
    {
        return 'cms_page';
    }

    /**
     * Get the ol li for the generated page of a business entity page pattern.
     *
     * @param BasePage $page
     *
     * @return string The html
     */
    public function cmsPageBusinessPagePatternSiteMap(BasePage $page)
    {
        $html = '';

        $urls = [];

        //the template link to the page
        $BusinessTemplate = $page;

        //
        if ($page instanceof BusinessTemplate) {
            //get the list of url of the children to avoid to have it twice.
            $childrenUrls = $this->getChildrenUrls($page);

            //services
            $BusinessTemplateHelper = $this->BusinessTemplateHelper;
            $pageHelper = $this->pageHelper;

            //the items allowed for the template
            $items = $BusinessTemplateHelper->getEntitiesAllowed($BusinessTemplate, $this->entityManager);

            //parse entities
            foreach ($items as $item) {
                $pageEntity = clone $BusinessTemplate;

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

                $itemsToAdd[$url] = [
                    'item'      => $item,
                    'url'       => $url,
                    'title'     => $title,
                    'generated' => $generated,
                ];

                unset($url);
            }

            //render the ol li
            $html .= '<ol>';
            foreach ($itemsToAdd as $item) {
                $itemUrl = $item['url'];
                $itemUrl = $this->router->generate('victoire_core_page_show', ['url' => $itemUrl]);
                $title = $item['title'];
                $generated = $item['generated'];

                //the class to identify the generated pages
                if ($generated) {
                    $class = 'generated';
                } else {
                    $class = '';
                }

                $html .= "<li><div class='".$class."'><a href='".$itemUrl."' title='".$title."'>".$title.'</a></div>';
            }
            $html .= '</ol>';
        }

        return $html;
    }

    /**
     * Get the list of urls of the children.
     *
     * @param BasePage $page
     *
     * @return aray of strings The list of urls
     */
    protected function getChildrenUrls(BasePage $page)
    {
        $urls = [];

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
