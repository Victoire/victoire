<?php

namespace Victoire\Bundle\PageBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\ViewReferenceBundle\Builder\BaseReferenceBuilder;

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
        $referenceId = $this->viewReferenceHelper->getViewReferenceId($view);
        return [
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'viewId'          => $view->getId(),
            'slug'            => $view->isHomepage() ? '' : $view->getSlug(),
            'name'            => $view->getName(),
            'viewNamespace'   => get_class($view),
            'view'            => $view,
        ];
    }
}
