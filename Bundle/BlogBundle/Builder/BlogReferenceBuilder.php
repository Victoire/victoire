<?php

namespace Victoire\Bundle\BlogBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\BaseReferenceBuilder;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

class BlogReferenceBuilder extends BaseReferenceBuilder
{
    /**
     * {@inheritdoc}
     */
    public function buildReference(View $view, EntityManager $em)
    {
        $referenceId = ViewReferenceHelper::generateViewReferenceId($view);
        $viewReference = new ViewReference();
        $viewReference->setId($referenceId);
        $viewReference->setLocale($view->getCurrentLocale());
        $viewReference->setName($view->getName());
        $viewReference->setViewId($view->getId());
        $viewReference->setSlug($view->getSlug());
        $viewReference->setViewNamespace($em->getClassMetadata(get_class($view))->name);
        if ($parent = $view->getParent()) {
            $viewReference->setParent(ViewReferenceHelper::generateViewReferenceId($parent));
        }

        return $viewReference;
    }
}
