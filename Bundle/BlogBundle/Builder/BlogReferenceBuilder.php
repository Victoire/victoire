<?php

namespace Victoire\Bundle\BlogBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\BaseReferenceBuilder;

class BlogReferenceBuilder extends BaseReferenceBuilder
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
            'viewNamespace'   => $em->getClassMetadata(get_class($view))->name,
            'view'            => $view,
        ];

        return $viewsReference;
    }
}
