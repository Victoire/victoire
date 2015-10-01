<?php

namespace Victoire\Bundle\BusinessPageBundle\Manager;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Manager\BaseReferenceBuilder;

/**
 * BusinessPageReferenceBuilder.
 */
class BusinessPageReferenceBuilder extends BaseReferenceBuilder
{
    public function buildReference(View $view, EntityManager $em)
    {
        $view->setUrl($this->urlBuilder->buildUrl($view));
        $referenceId = $this->viewReferenceHelper->getViewReferenceId($view);
        $viewsReferences[] = [
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'viewId'          => $view->getId(),
            'patternId'       => $view->getTemplate()->getId(),
            'url'             => $view->getUrl(),
            'name'            => $view->getName(),
            'entityId'        => $view->getBusinessEntity()->getId(),
            'entityNamespace' => $em->getClassMetadata(get_class($view->getBusinessEntity()))->name,
            'viewNamespace'   => $em->getClassMetadata(get_class($view))->name,
            'view'            => $view,
        ];

        return $viewsReferences;
    }
}
