<?php

namespace Victoire\Bundle\BusinessEntityPageBundle\Manager;

use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessEntityPageBundle\Manager\Interfaces\BusinessPageReferenceBuilderInterface;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Manager\BaseReferenceBuilder;

/**
* BusinessPageReferenceBuilder
*/
class BusinessPageReferenceBuilder extends BaseReferenceBuilder implements BusinessPageReferenceBuilderInterface
{
    public function buildReference(BusinessPage $view)
    {

        $view->setUrl($this->urlBuilder->buildUrl($view));
        $referenceId = $this->getViewCacheHelper()->getViewReferenceId($view);
        $viewsReferences[] = array(
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'viewId'          => $view->getId(),
            'patternId'       => $view->getTemplate()->getId(),
            'url'             => $view->getUrl(),
            'name'            => $view->getName(),
            'entityId'        => $view->getBusinessEntity()->getId(),
            'entityNamespace' => $this->getEntityManager()->getClassMetadata(get_class($view->getBusinessEntity()))->name,
            'viewNamespace'   => $this->getEntityManager()->getClassMetadata(get_class($view))->name,
            'type'            => $view::TYPE,
        );
        return $viewsReferences;
    }
}
