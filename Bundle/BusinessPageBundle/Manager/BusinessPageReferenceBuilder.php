<?php

namespace Victoire\Bundle\BusinessPageBundle\Manager;

use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Manager\Interfaces\BusinessPageReferenceBuilderInterface;
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
