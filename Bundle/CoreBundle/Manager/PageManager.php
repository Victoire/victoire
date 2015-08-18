<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Victoire\Bundle\CoreBundle\Entity\View;

/**
* PageManager
*/
class PageManager extends BaseViewManager implements ViewManagerInterface
{
    public function buildReference(View $view){
        $viewsReferences = array();
        $referenceId = $this->viewCacheHelper->getViewReferenceId($view);
        $viewsReferences[$view->getUrl().$view->getLocale()] = array(
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'viewId'          => $view->getId(),
            'url'             => $view->getUrl(),
            'name'            => $view->getName(),
            'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
        );
        return $viewsReferences;
    }
}