<?php

namespace Victoire\Bundle\PageBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\BaseReferenceBuilder;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * PageManager.
 */
class PageReferenceBuilder extends BaseReferenceBuilder
{
    /**
     * {@inheritdoc}
     */
    public function buildReference(View $view, EntityManager $em)
    {
        $referenceId = ViewReferenceHelper::generateViewReferenceId($view);

        $viewReference = new ViewReference();
        $viewReference->setId($referenceId);
        $viewReference->setLocale($view->getLocale());
        $viewReference->setName($view->getName());
        $viewReference->setViewId($view->getId());
        $viewReference->setSlug($view->isHomepage() ? '' : $view->getSlug());
        $viewReference->setViewNamespace(get_class($view));
        if ($parent = $view->getParent()) {
            $viewReference->setParent(ViewReferenceHelper::generateViewReferenceId($parent));
        }

        return $viewReference;
    }
}
