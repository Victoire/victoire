<?php

namespace Victoire\Bundle\BusinessPageBundle\Manager;

use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\BusinessPageBundle\Manager\Interfaces\VirtualBusinessPageReferenceBuilderInterface;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Manager\BaseReferenceBuilder;
use Victoire\Bundle\CoreBundle\Manager\Interfaces\ReferenceBuilderInterface;

/**
* VirtualBusinessPageReferenceBuilder
*/
class VirtualBusinessPageReferenceBuilder extends BaseReferenceBuilder implements VirtualBusinessPageReferenceBuilderInterface
{
    public function buildReference(VirtualBusinessPage $view){

        $referenceId = $this->getViewCacheHelper()->getViewReferenceId($view);
        $viewsReference[] = array(
            'id'              => $referenceId,
            'locale'          => $view->getLocale(),
            'patternId'       => $view->getTemplate()->getId(),
            'url'             => $view->getUrl(),
            'name'            => $view->getName(),
            'entityId'        => $view->getBusinessEntity()->getId(),
            'entityNamespace' => $this->getEntityManager()->getClassMetadata(get_class($view->getBusinessEntity()))->name,
            'viewNamespace'   => $this->getEntityManager()->getClassMetadata(get_class($view))->name,
            'type'            => $view::TYPE,
            'view'            => $view,
        );

        return $viewsReference;
    }
}
