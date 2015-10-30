<?php

namespace Victoire\Bundle\BusinessPageBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\BaseReferenceBuilder;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\BusinessPageReference;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * BusinessPageReferenceBuilder.
 */
class BusinessPageReferenceBuilder extends BaseReferenceBuilder
{
    /**
     * @inheritdoc
     */
    public function buildReference(View $view, EntityManager $em)
    {
        $view->setUrl($this->urlBuilder->buildUrl($view));
        $referenceId = ViewReferenceHelper::generateViewReferenceId($view);

        $viewReference = new BusinessPageReference();

        $viewReference->setId($referenceId);
        $viewReference->setLocale($view->getLocale());
        $viewReference->setViewId($view->getId());
        $viewReference->setPatternId($view->getTemplate()->getId());
        $viewReference->setSlug($view->getSlug());
        $viewReference->setName($view->getName());
        $viewReference->setEntityId($view->getBusinessEntity()->getId());
        $viewReference->setEntityNamespace($em->getClassMetadata(get_class($view->getBusinessEntity()))->name);
        $viewReference->setViewNamespace($em->getClassMetadata(get_class($view))->name);
        $viewReference->setView($view);

        return $viewReference;
    }
}
