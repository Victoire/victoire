<?php

namespace Victoire\Bundle\PageBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\ViewReferenceBundle\Builder\BaseReferenceBuilder;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * PageManager.
 */
class PageReferenceBuilder extends BaseReferenceBuilder
{
    /**
     * @inheritdoc
     */
    public function buildReference(View $view, EntityManager $em)
    {
        $view->setUrl($this->urlBuilder->buildUrl($view));
        $referenceId = ViewReferenceHelper::generateViewReferenceId($view);

        $viewReference = new ViewReference();
        $viewReference->setId($referenceId);
        $viewReference->setLocale($view->getLocale());
        $viewReference->setViewId($view->getId());
        $viewReference->setSlug($view->isHomepage() ? '' : $view->getSlug());
        $viewReference->setViewNamespace(get_class($view));

        return $viewReference;
    }
}
