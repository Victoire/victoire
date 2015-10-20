<?php

namespace Victoire\Bundle\BusinessPageBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\BaseReferenceBuilder;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * VirtualBusinessPageReferenceBuilder.
 */
class VirtualBusinessPageReferenceBuilder extends BaseReferenceBuilder
{
    /**
     * @inheritdoc
     */
    public function buildReference(View $view, EntityManager $em)
    {
        if ($view->getBusinessEntity() instanceof Article) {
            return [];
        }

        $viewReference = new ViewReference();
        $viewReference->setId(ViewReferenceHelper::generateViewReferenceId($view));
        $viewReference->setLocale($view->getLocale());
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
