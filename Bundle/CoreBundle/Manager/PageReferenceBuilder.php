<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Victoire\Bundle\CoreBundle\Manager\Interfaces\PageReferenceBuilderInterface;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
* PageManager
*/
class PageReferenceBuilder extends BaseReferenceBuilder implements PageReferenceBuilderInterface
{
    public function buildReference(Page $view){
        $view->setUrl($this->urlBuilder->buildUrl($view));
        $referenceId = $this->viewCacheHelper->getViewReferenceId($view);
        $viewsReference[] = array(
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'viewId'          => $view->getId(),
            'url'             => $view->getUrl(),
            'name'            => $view->getName(),
            'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
            'view'            => $view,
        );

        return $viewsReference;

    }
}
