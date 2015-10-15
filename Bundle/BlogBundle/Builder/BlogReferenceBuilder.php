<?php

namespace Victoire\Bundle\BlogBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\BaseReferenceBuilder;

class BlogReferenceBuilder extends BaseReferenceBuilder
{
    /**
     * @inheritdoc
     */
    public function buildReference(View $view, EntityManager $em)
    {
        $view->setUrl($this->urlBuilder->buildUrl($view));
        $referenceId = $this->viewReferenceHelper->getViewReferenceId($view);

        return [
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'viewId'          => $view->getId(),
            'slug'            => $view->getSlug(),
            'name'            => $view->getName(),
            'viewNamespace'   => $em->getClassMetadata(get_class($view))->name,
            'view'            => $view,
        ];
    }
}
