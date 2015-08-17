<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Victoire\Bundle\CoreBundle\Entity\View;

/**
* TemplateManager
*/
class TemplateManager extends BaseViewManager implements ViewManagerInterface
{
    public function buildReference(View $view){
        $viewsReferences = array();
        $referenceId = $this->viewCacheHelper->getViewReferenceId($view);
        $viewsReferences[$referenceId.$view->getLocale()] = array(
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'name'            => $view->getName(),
            'viewId'          => $view->getId(),
            'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
        );
        return $viewsReferences;
    }
}