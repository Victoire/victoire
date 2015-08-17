<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\BusinessEntityPageBundle\Helper\BusinessEntityPageHelper;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\CoreBundle\Helper\ViewHelper;

/**
* BusinessEntityPagePatternManager
*/
class BusinessEntityPagePatternManager extends BaseViewManager implements ViewManagerInterface
{
    private $businessEntityPageHelper;
    private $businessEntityHelper;

    public function buildReference(View $view, $entity = null){
        $viewsReferences = array();
        if ($entity) {
            if($this->businessEntityPageHelper->isEntityAllowed($view, $entity)){
                $currentPattern = clone $view;
                $page = $this->businessEntityPageHelper->generateEntityPageFromPattern($currentPattern, $entity);
                $this->businessEntityPageHelper->updatePageParametersByEntity($page, $entity);
                $referenceId = $this->viewCacheHelper->getViewReferenceId($view, $entity);
                $viewsReferences[$page->getUrl().$page->getLocale()] = array(
                    'id'              => $referenceId,
                    'url'             => $page->getUrl(),
                    'name'            => $page->getName(),
                    'locale'          => $page->getLocale(),
                    'patternId'       => $page->getTemplate()->getId(),
                    'entityId'        => $entity->getId(),
                    'entityNamespace' => $this->em->getClassMetadata(get_class($entity))->name,
                    'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
                );
            }
        } else {
            $referenceId = $this->viewCacheHelper->getViewReferenceId($view);
            $viewsReferences[$view->getUrl().$view->getLocale()] = array(
                'id'              => $referenceId,
                'url'             => $view->getUrl(),
                'name'            => $view->getName(),
                'locale'          => $view->getLocale(),
                'viewId'          => $view->getId(),
                'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
            );
            $businessEntities = $this->businessEntityHelper->getBusinessEntities();

            foreach ($businessEntities as $businessEntity) {
                $properties = $this->businessEntityPageHelper->getBusinessProperties($businessEntity);

                //find business identifiers of the current businessEntity
                $selectableProperties = array('id');
                foreach ($properties as $property) {
                    if ($property->getType() === 'businessParameter') {
                        $selectableProperties[] = $property->getEntityProperty();
                    }
                }

                $entities = $this->businessEntityPageHelper->getEntitiesAllowed($view);

                // for each business entity
                foreach ($entities as $entity) {
                    // only if related pattern entity is the current entity
                    if ($view->getBusinessEntityName() === $businessEntity->getName()) {
                        $currentPattern = clone $view;
                        $page = $this->businessEntityPageHelper->generateEntityPageFromPattern($currentPattern, $entity);
                        $this->businessEntityPageHelper->updatePageParametersByEntity($page, $entity);
                        $referenceId = $this->viewCacheHelper->getViewReferenceId($view, $entity);
                        $viewsReferences[$page->getUrl().$view->getLocale()] = array(
                            'id'              => $referenceId,
                            'url'             => $page->getUrl(),
                            'name'             => $page->getName(),
                            'locale'          => $page->getLocale(),
                            'patternId'       => $page->getTemplate()->getId(),
                            'entityId'        => $entity->getId(),
                            'entityNamespace' => $this->em->getClassMetadata(get_class($entity))->name,
                            'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
                        );
                    }
                    //I refresh this partial entity from em. If I don't do it, everytime I'll request this entity from em it'll be partially populated
                    $this->em->refresh($entity);
                }
            }
        }
        return $viewsReferences;
    }

    public function setBusinessEntityPageHelper(BusinessEntityPageHelper $businessEntityPageHelper)
    {
        $this->businessEntityPageHelper = $businessEntityPageHelper;

        return $this;
    }

    public function setBusinessEntityHelper(BusinessEntityHelper $businessEntityHelper)
    {
        $this->businessEntityHelper = $businessEntityHelper;

        return $this;
    }
}