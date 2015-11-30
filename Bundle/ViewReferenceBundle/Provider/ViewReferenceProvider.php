<?php

namespace Victoire\Bundle\ViewReferenceBundle\Provider;

use Doctrine\ORM\EntityManager;
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
     * @param View[] $views
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
        foreach ($views as $key => $view) {
            if ($view instanceof BusinessTemplate) {
                $entities = $this->businessPageHelper->getEntitiesAllowed($view, $em);

                // for each business entity
                foreach ($entities as $k => $entity) {
                    $currentPattern = clone $view;
                    $page = $this->businessPageBuilder->generateEntityPageFromTemplate($currentPattern, $entity, $em);
                    $this->businessPageBuilder->updatePageParametersByEntity($page, $entity);
                    if (!array_key_exists(ViewReferenceHelper::generateViewReferenceId($page, $entity), $businessPages)) {
                        $referencableViews[] = ['view' => $page];
                    }
                }
            } elseif ($view instanceof WebViewInterface) {
                $referencableViews[$key] = ['view' => $view];
            }
            if (isset($referencableViews[$key]) && $view->hasChildren()) {
                $referencableViews[$key]['children'] = $this->getReferencableViews($view->getChildren(), $em);
            }
        }

        return $referencableViews;
    }

    public function findBusinessPages($tree) {
        $businessPages = [];
        foreach ($tree as $key => $view) {
            if ($view instanceof BusinessPage) {
                $businessPages[ViewReferenceHelper::generateViewReferenceId($view, $view->getBusinessEntity())] = $view;
            }
            if ($view->hasChildren()) {
                self::findBusinessPages($view->getChildren());
            }
        }

        return $businessPages;
    }
}
