<?php

namespace Victoire\Bundle\CoreBundle\Helper;

use Doctrine\Orm\EntityManager;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter as BETParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\ViewReferenceBundle\Builder\ViewReferenceBuilder;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Provider\ViewReferenceProvider;

/**
 * Page helper
 * ref: victoire_core.view_helper.
 */
class ViewHelper
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var ViewReferenceProvider
     */
    private $viewReferenceProvider;
    /**
     * @var ViewReferenceHelper
     */
    private $viewReferenceHelper;

    /**
     * Constructor.
     *
     * @internal param $ViewManagerChain $$viewManagerChain
     * @param EntityManager $entityManager
     * @param ViewReferenceHelper $viewReferenceHelper
     * @param ViewReferenceProvider $viewReferenceProvider
     */
    public function __construct(
        EntityManager $entityManager,
        ViewReferenceProvider $viewReferenceProvider,
        ViewReferenceHelper $viewReferenceHelper
    ) {

        $this->entityManager = $entityManager;
        $this->viewReferenceProvider = $viewReferenceProvider;
        $this->viewReferenceHelper = $viewReferenceHelper;
    }

    /**
     * @return WebViewInterface[]
     */
    public function buildViewsReferences()
    {
        $viewsHierarchy = $this->entityManager->getRepository('VictoireCoreBundle:View')->getRootNodes();
        $views = $this->viewReferenceProvider->getReferencableViews($viewsHierarchy, $this->entityManager);

        $this->viewReferenceHelper->buildViewReferenceRecursively($views, $this->entityManager);

        return $views;
    }
}
