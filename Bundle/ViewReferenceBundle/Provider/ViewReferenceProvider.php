<?php

namespace Victoire\Bundle\ViewReferenceBundle\Provider;

use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIBusinessEntity;
use Victoire\Bundle\BusinessPageBundle\Builder\BusinessPageBuilder;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;

/**
 * @property BusinessPageHelper businessPageHelper
 * @property BusinessPageBuilder businessPageBuilder
 * ref. victoire_view_reference.provider
 */
class ViewReferenceProvider
{
    protected $businessPageHelper;
    protected $businessPageBuilder;

    /**
     * @param BusinessPageHelper  $businessPageHelper
     * @param BusinessPageBuilder $businessPageBuilder
     */
    public function __construct(BusinessPageHelper $businessPageHelper, BusinessPageBuilder $businessPageBuilder)
    {
        $this->businessPageHelper = $businessPageHelper;
        $this->businessPageBuilder = $businessPageBuilder;
    }

    /**
     * @param View[]        $views
     * @param EntityManager $em
     *
     * @return WebViewInterface[]
     */
    public function getReferencableViews($views, EntityManager $em)
    {
        $referencableViews = [];
        if (!$views instanceof \Traversable && !is_array($views)) {
            $views = [$views];
        }

        $businessPages = $this->findBusinessPages($views);
        foreach ($views as $view) {
            if ($view instanceof BusinessTemplate) {
                $entities = $this->businessPageHelper->getEntitiesAllowed($view, $em);

                // for each business entity
                foreach ($entities as $k => $entity) {
                    $currentTemplate = clone $view;
                    $page = $this->businessPageBuilder->generateEntityPageFromTemplate($currentTemplate, $entity, $em);
                    $this->businessPageBuilder->updatePageParametersByEntity($page, $entity);
                    $entityId = null;
                    if (method_exists($entity, 'getId')) {
                        $entityId = $entity->getId();
                    } elseif ($page->getBusinessEntity()->getType() === APIBusinessEntity::TYPE) {
                        $accessor = new PropertyAccessor();
                        $entityId = $accessor->getValue($entity, $page->getBusinessEntity()->getBusinessParameters()->first()->getName());
                    }
                    if (!array_key_exists(ViewReferenceHelper::generateViewReferenceId($page, $entityId), $businessPages)) {
                        $referencableViews[] = ['view' => $page];
                    }
                }
            } elseif ($view instanceof WebViewInterface) {
                $referencableView = ['view' => $view];
                if ($view->getChildren()) {
                    $referencableView['children'] = $this->getReferencableViews($view->getChildren(), $em);
                }
                $referencableViews[] = $referencableView;
            }
        }

        return $referencableViews;
    }

    public function findBusinessPages($tree)
    {
        $businessPages = [];
        foreach ($tree as $view) {
            if ($view instanceof BusinessPage) {
                $businessPages[ViewReferenceHelper::generateViewReferenceId($view, $view->getEntity())] = $view;
            }
            if ($view->hasChildren()) {
                self::findBusinessPages($view->getChildren());
            }
        }

        return $businessPages;
    }
}
