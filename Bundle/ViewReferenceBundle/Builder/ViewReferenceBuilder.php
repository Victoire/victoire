<?php

namespace Victoire\Bundle\ViewReferenceBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\ViewReferenceBundle\Builder\Chain\ViewReferenceBuilderChain;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * View Reference builder
 * ref: victoire_view_reference.builder.
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
     * @return ViewReference
     */
    public function buildViewReference(View $view, EntityManager $em = null)
    {
        $viewReferenceBuilder = $this->viewReferenceBuilderChain->getViewReferenceBuilder($view);
        $viewReference = $viewReferenceBuilder->buildReference($view, $em);

        return $viewReference;
    }
}
