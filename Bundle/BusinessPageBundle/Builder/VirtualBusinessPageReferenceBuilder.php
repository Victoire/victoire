<?php

namespace Victoire\Bundle\BusinessPageBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\BaseReferenceBuilder;

/**
 * VirtualBusinessPageReferenceBuilder.
 */
class VirtualBusinessPageReferenceBuilder extends BaseReferenceBuilder
{
    public function buildReference(View $view, EntityManager $em)
    {
        if ($view->getBusinessEntity() instanceof Article) {
            return [];
        }
        $referenceId = $this->viewReferenceHelper->getViewReferenceId($view);
        $viewsReference[] = [
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'patternId'       => $view->getTemplate()->getId(),
            'url'             => $view->getUrl(),
            'name'            => $view->getName(),
            'entityId'        => $view->getBusinessEntity()->getId(),
            'entityNamespace' => $em->getClassMetadata(get_class($view->getBusinessEntity()))->name,
            'viewNamespace'   => $em->getClassMetadata(get_class($view))->name,
            'view'            => $view,
        ];

        return $viewsReference;
    }
}
