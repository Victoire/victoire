<?php

namespace Victoire\Bundle\PageBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
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
            'cms_page_css'                           => new \Twig_Function_Method($this, 'cmsPageCss', ['is_safe' => ['html']]),
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
