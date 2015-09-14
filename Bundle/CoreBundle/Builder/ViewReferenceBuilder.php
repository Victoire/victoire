<?php

namespace Victoire\Bundle\CoreBundle\Builder;

use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\CoreBundle\Manager\Chain\ViewReferenceBuilderChain;


/**
 * Page helper
 * ref: victoire_core.view_reference_builder
 */
class ViewReferenceBuilder
{
    protected $viewReferenceBuilderChain;

    public function __construct(ViewReferenceBuilderChain $viewReferenceBuilderChain)
    {
        $this->viewReferenceBuilderChain = $viewReferenceBuilderChain;
    }



    /**
     * compute the viewReference relative to a View + entity
     * @param View                $view
     * @param array|[array] $entity
     *
     * @return array
     */
    public function buildViewReference(View $view, $entity = null, $em = null)
    {
        $viewManager = $this->viewReferenceBuilderChain->getViewReferenceBuilder($view);
        $viewReferences = $viewManager->buildReference($view, $entity, $em);

        return $viewReferences;
    }


}
