<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Manager\Interfaces\PageReferenceBuilderInterface;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
* PageManager
*/
class PageReferenceBuilder extends BaseReferenceBuilder
{
    public function buildReference(View $view, EntityManager $em){
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
