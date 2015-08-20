<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Victoire\Bundle\CoreBundle\Entity\View;

/**
* BusinessEntityPageManager
*/
class BusinessEntityPageManager extends BaseViewManager implements ViewManagerInterface
{
    public function buildReference(View $view){
        $viewsReferences = array();
        $referenceId = $this->viewCacheHelper->getViewReferenceId($view);
        $viewsReferences[$view->getUrl().$view->getLocale()] = array(
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'viewId'          => $view->getId(),
            'patternId'       => $view->getTemplate()->getId(),
            'url'             => $view->getUrl(),
            'name'            => $view->getName(),
            'entityId'        => $view->getBusinessEntity()->getId(),
            'entityNamespace' => $this->em->getClassMetadata(get_class($view->getBusinessEntity()))->name,
            'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
        );
        return $viewsReferences;
    }
}