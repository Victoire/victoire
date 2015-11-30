<?php

namespace Victoire\Bundle\ViewReferenceBundle\Helper;

use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\ViewReferenceBundle\Builder\ViewReferenceBuilder;

/**
 * ref: victoire_view_reference.helper
 */
class ViewReferenceHelper
{
    /**
     * @var ViewReferenceBuilder
     */
    private $viewReferenceBuilder;

    /**
     * Constructor.
     * @param ViewReferenceBuilder $viewReferenceBuilder
     */
    public function __construct(ViewReferenceBuilder $viewReferenceBuilder) {
        $this->viewReferenceBuilder = $viewReferenceBuilder;
    }
    /**
     * @param View  $view
     * @param mixed $entity
     *
     * @return string
     */
    public static function generateViewReferenceId(View $view, $entity = null)
    {
        $id = $view->getId();
        if ($view instanceof BusinessPage) {
            $id = $view->getTemplate()->getId();
            $entity = $view->getBusinessEntity();

        } elseif (!$view instanceof WebViewInterface) {
            return $view->getId();
        }

        $refId = sprintf('ref_%s', $id);
        if ($entity) {
            $refId .= '_'.$entity->getId();
        }

        return $refId;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public static function buildXpath(array $parameters)
    {
        $arguments = [];
        foreach ($parameters as $key => $value) {
            $arguments[$key] = '@'.$key.'="'.$value.'"';
        }

        return '//viewReference['.implode(' and ', $arguments).']';
    }

    /**
     * @param [] $tree
     */
    public function buildViewReferenceRecursively($tree, $entityManager) {
        foreach ($tree as $branch) {
            /** @var WebViewInterface $view */
            $view = $branch['view'];
            $view->setViewReference($this->viewReferenceBuilder->buildViewReference($view, $entityManager));
            if (!empty($branch['children'])) {
                /** @var WebViewInterface $children */
                $children = $branch['children'];
                $this->buildViewReferenceRecursively($children, $entityManager);
            }
        }
    }
}
