<?php

namespace Victoire\Bundle\ViewReferenceBundle\Helper;

use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\I18nBundle\Entity\ViewTranslation;
use Victoire\Bundle\ViewReferenceBundle\Builder\ViewReferenceBuilder;

/**
 * ref: victoire_view_reference.helper.
 */
class ViewReferenceHelper
{
    /**
     * @var ViewReferenceBuilder
     */
    private $viewReferenceBuilder;

    /**
     * Constructor.
     *
     * @param ViewReferenceBuilder $viewReferenceBuilder
     */
    public function __construct(ViewReferenceBuilder $viewReferenceBuilder)
    {
        $this->viewReferenceBuilder = $viewReferenceBuilder;
    }

    /**
     * @param View  $view
     * @param mixed $entityId
     *
     * @return string
     */
    public static function generateViewReferenceId(View $view, $entityId = null)
    {
        $id = $view->getId();
        if ($view instanceof BusinessPage && $view->getEntity()) {
            $id = $view->getTemplate()->getId();
            $accessor = new PropertyAccessor();
            $entity = $view->getEntity();
            if (method_exists($entity, 'getId')) {
                $entityId = $entity->getId();
            } else {
                $entityId = $accessor->getValue($entity, $view->getBusinessEntity()->getBusinessIdentifiers()->first()->getName());
            }
        } elseif (!$view instanceof WebViewInterface) {
            return $view->getId();
        }

        $refId = sprintf('ref_%s', $id);
        if ($entityId) {
            $refId .= '_'.$entityId;
        }
        /** @var string $currentLocale */
        if ('' != $currentLocale = $view->getCurrentLocale()) {
            $refId .= '_'.$currentLocale;
        }

        return $refId;
    }

    /**
     * @param WebViewInterface[] $tree
     */
    public function buildViewReferenceRecursively($tree, EntityManager $entityManager, $isRoot = true)
    {
        foreach ($tree as $branch) {
            /** @var View $view */
            $view = $branch['view'];
            /** @var ViewTranslation $translation */
            foreach ($view->getTranslations() as $translation) {
                if (true === $isRoot || $translation->getLocale() == $view->getParent()->getCurrentLocale()) {
                    $view->setCurrentLocale($translation->getLocale());
                    $viewReference = $this->viewReferenceBuilder->buildViewReference($view, $entityManager);
                    if (!empty($branch['children'])) {
                        /** @var WebViewInterface $children */
                        $children = $branch['children'];
                        $this->buildViewReferenceRecursively($children, $entityManager, false);
                    }
                    $view->setReference($viewReference, $translation->getLocale());
                }
            }
        }
    }
}
