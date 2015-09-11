<?php

namespace Victoire\Bundle\BusinessPageBundle\Manager;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\BusinessPageBundle\Manager\Interfaces\BusinessPageReferenceBuilderInterface;
use Victoire\Bundle\BusinessPageBundle\Manager\Interfaces\BusinessTemplateReferenceBuilderInterface;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Victoire\Bundle\CoreBundle\Manager\BaseReferenceBuilder;

/**
* BusinessTemplateReferenceBuilder
*/
class BusinessTemplateReferenceBuilder extends BaseReferenceBuilder implements BusinessTemplateReferenceBuilderInterface
{
    protected $virtualBusinessPageReferenceBuilder;
    protected $businessEntityHelper;
    protected $businessEntityPageHelper;

    public function __construct(
        ViewCacheHelper $viewCacheHelper,
        EntityManager $em,
        UrlBuilder $urlBuilder,
        VirtualBusinessPageReferenceBuilder $virtualBusinessPageReferenceBuilder,
        BusinessEntityHelper $businessEntityHelper,
        BusinessPageHelper $businessEntityPageHelper
    )
    {
        parent::__construct($viewCacheHelper, $em, $urlBuilder);
        $this->virtualBusinessPageReferenceBuilder = $virtualBusinessPageReferenceBuilder;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->businessEntityPageHelper = $businessEntityPageHelper;
    }

    public function buildReference(BusinessTemplate $view)
    {
        $viewsReferences = [];
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
                if ($view->getBusinessEntityId() === $businessEntity->getId()) {
                    $currentPattern = clone $view;
                    $page = $this->businessEntityPageHelper->generateEntityPageFromPattern($currentPattern, $entity);
                    $this->businessEntityPageHelper->updatePageParametersByEntity($page, $entity);
                    $referenceId = $this->getViewCacheHelper()->getViewReferenceId($view, $entity);

                    $viewsReferences = $viewsReferences + $this->virtualBusinessPageReferenceBuilder->buildReference($page);
                }
                //I refresh this partial entity from em. If I don't do it, everytime I'll request this entity from em it'll be partially populated
                $this->getEntityManager()->refresh($entity);
            }
        }

        return $viewsReferences;
    }
}
