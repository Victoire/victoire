<?php

namespace Victoire\Bundle\CoreBundle\Finder;

use Victoire\Bundle\CoreBundle\Manager\ViewManagerInterface;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\TwigBundle\Entity\ErrorPage;

class ViewManagerFinder
{
    private $businessEntityPagePatternManager;
    private $businessEntityPageManager;
    private $errorPageManager;
    private $templateManager;
    private $baseViewManager;

    public function __construct(
        ViewManagerInterface $businessEntityPagePatternManager,
        ViewManagerInterface $businessEntityPageManager,
        ViewManagerInterface $errorPageManager,
        ViewManagerInterface $templateManager,
        ViewManagerInterface $baseViewManager
        )
    {
        $this->businessEntityPagePatternManager = $businessEntityPagePatternManager;
        $this->businessEntityPageManager = $businessEntityPageManager;
        $this->errorPageManager = $errorPageManager;
        $this->templateManager = $templateManager;
        $this->baseViewManager = $baseViewManager;
    }

    /**
     * get the correct manager for a view
     * @param View $view the view that we want the manager
     * @return ViewManager the correct Manager
     **/
    public function getViewManager(View $view)
    {
        if($view instanceof BusinessEntityPagePattern) {
            $viewManager = $this->businessEntityPagePatternManager;
        }elseif ($view instanceof BusinessEntityPage) {
            $viewManager = $this->businessEntityPageManager;
        }elseif ($view instanceof ErrorPage) {
            $viewManager = $this->errorPageManager;
        }elseif ($view instanceof Template) {
            $viewManager = $this->templateManager;
        }else{
            $viewManager = $this->baseViewManager;
        }

        return $viewManager;
    }
}