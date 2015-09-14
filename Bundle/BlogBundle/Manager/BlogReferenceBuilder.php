<?php
namespace Victoire\Bundle\BlogBundle\Manager;


use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\BlogBundle\Manager\Interfaces\BlogReferenceBuilderInterface;
use Victoire\Bundle\CoreBundle\Manager\BaseReferenceBuilder;

class BlogReferenceBuilder extends BaseReferenceBuilder implements BlogReferenceBuilderInterface
{
    public function buildReference(Blog $view){
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
