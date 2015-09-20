<?php
namespace Victoire\Bundle\BlogBundle\Manager;


use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Manager\BaseReferenceBuilder;

class BlogReferenceBuilder extends BaseReferenceBuilder
{
    public function buildReference(View $view, EntityManager $em) {
        $view->setUrl($this->urlBuilder->buildUrl($view));
        $referenceId = $this->viewReferenceHelper->getViewReferenceId($view);
        $viewsReference[] = array(
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'viewId'          => $view->getId(),
            'url'             => $view->getUrl(),
            'name'            => $view->getName(),
            'viewNamespace'   => $em->getClassMetadata(get_class($view))->name,
            'view'            => $view,
        );

        return $viewsReference;
    }
}
