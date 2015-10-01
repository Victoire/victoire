<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * PageManager.
 */
class PageReferenceBuilder extends BaseReferenceBuilder
{
    public function buildReference(View $view, EntityManager $em)
    {
        $view->setUrl($this->urlBuilder->buildUrl($view));
        $referenceId = $this->viewReferenceHelper->getViewReferenceId($view);
        $viewsReference[] = [
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'viewId'          => $view->getId(),
            'url'             => $view->getUrl(),
            'name'            => $view->getName(),
            'viewNamespace'   => get_class($view),
            'view'            => $view,
        ];

        return $viewsReference;
    }
}
