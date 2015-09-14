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
        $referenceId = $this->viewReferenceHelper->getViewReferenceId($view);
        $viewsReference[] = array(
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'viewId'          => $view->getId(),
            'url'             => $view->getUrl(),
            'name'            => $view->getName(),
            'viewNamespace'   => get_class($view),
            'view'            => $view,
        );

        return $viewsReference;

    }
}
