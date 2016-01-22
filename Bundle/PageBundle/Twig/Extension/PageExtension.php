<?php

namespace Victoire\Bundle\PageBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\CoreBundle\Builder\ViewCssBuilder;
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
     * @param ViewCssBuilder     $viewCssBuilder
     * @param EntityManager      $entityManager
     */
    public function __construct(
        BusinessPageHelper $BusinessTemplateHelper,
        Router $router,
        PageHelper $pageHelper,
        CurrentViewHelper $currentViewHelper,
        ViewCssBuilder $viewCssBuilder,
        EntityManager $entityManager
    ) {
        $this->BusinessTemplateHelper = $BusinessTemplateHelper;
        $this->router = $router;
        $this->pageHelper = $pageHelper;
        $this->currentViewHelper = $currentViewHelper;
        $this->viewCssBuilder = $viewCssBuilder;
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
            new \Twig_SimpleFunction('cms_page_css', [$this, 'cmsPageCss'], ['is_safe' => ['html']]),
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
     * Construct CSS link markup for the style of all the Widgets contained in the current View.
     *
     * @return string
     */
    public function cmsPageCss()
    {
        $currentView = $this->currentViewHelper->getCurrentView();
        if (!$currentView || !$this->viewCssBuilder->cssFileExists($currentView)) {
            return '';
        }

        return sprintf(
            '<link href="%s" ng-href="%s" rel="stylesheet" type="text/css" rel="stylesheet"/>',
            $this->viewCssBuilder->getHref($currentView),
            $this->viewCssBuilder->getAngularHref($currentView)
        );
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

    /**
     * Return the current viewReference->id.
     *
     * @return string
     */
    public function victoireCurrentPageReference()
    {
        $currentView = $this->currentViewHelper;

        return $currentView->getMainCurrentView()->getReference()->getId();
    }
}
