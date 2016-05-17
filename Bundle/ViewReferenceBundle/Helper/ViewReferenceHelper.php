<?php

namespace Victoire\Bundle\ViewReferenceBundle\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
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
        if ($view->getLocale() != '') {
            $refId .= '_'.$view->getLocale();
        }

        return $refId;
    }

    /**
     * @param [] $tree
     */
    public function buildViewReferenceRecursively($tree, EntityManager $entityManager)
    {
        foreach ($tree as $branch) {
            /** @var WebViewInterface $view */
            $view = $branch['view'];
            if (!$view instanceof VirtualBusinessPage) {
                $viewReferences = [];
                /* @var EntityRepository $viewRepo */
                $viewTranslationRepo = $entityManager->getRepository(ViewTranslation::class);
                foreach ($viewTranslationRepo->findByObject($view) as $translation) {
                    $view->setTranslatableLocale($translation->getLocale());
                    $entityManager->refresh($view);
                    $viewReferences[$translation->getLocale()] = $this->viewReferenceBuilder->buildViewReference($view, $entityManager);
                }
                $view->setReferences($viewReferences);
            }
            if (!empty($branch['children'])) {
                /** @var WebViewInterface $children */
                $children = $branch['children'];
                $this->buildViewReferenceRecursively($children, $entityManager);
            }
        }
    }
}
