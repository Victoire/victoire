<?php

namespace Victoire\Bundle\CoreBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\CoreBundle\Manager\Chain\ViewReferenceBuilderChain;

/**
 * Page helper
 * ref: victoire_core.view_reference_builder.
 */
class ViewReferenceBuilder
{
    protected $viewReferenceBuilderChain;

    public function __construct(ViewReferenceBuilderChain $viewReferenceBuilderChain)
    {
        $this->viewReferenceBuilderChain = $viewReferenceBuilderChain;
    }

    /**
     * compute the viewReference relative to a View + entity.
     *
     * @param WebViewInterface $view
     *
     * @return array
     */
    public function buildViewReference(WebViewInterface $view, EntityManager $em = null)
    {
        $viewManager = $this->viewReferenceBuilderChain->getViewReferenceBuilder($view);
        $viewReferences = $viewManager->buildReference($view, $em);

        return $viewReferences;
    }
}
