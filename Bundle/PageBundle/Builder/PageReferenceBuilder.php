<?php

namespace Victoire\Bundle\PageBundle\Builder;

use Doctrine\Common\Util\ClassUtils;
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
        /* @var \Victoire\Bundle\PageBundle\Entity\Page $view */
        $referenceId = ViewReferenceHelper::generateViewReferenceId($view);

        $viewReference = new ViewReference();
        $viewReference->setId($referenceId);
        $viewReference->setLocale($view->getCurrentLocale());
        $viewReference->setName($view->getName());
        $viewReference->setViewId($view->getId());
        $viewReference->setSlug($view->isHomepage() ? '' : $view->getSlug());
        $viewReference->setViewNamespace(ClassUtils::getClass($view));
        if ($parent = $view->getParent()) {
            $parent->translate($view->getCurrentLocale());
            $viewReference->setParent(ViewReferenceHelper::generateViewReferenceId($parent));
        }

        return $viewReference;
    }
}
