<?php

namespace Victoire\Bundle\WidgetMapBundle\Warmer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessEntityBundle\Resolver\BusinessEntityResolver;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Entity\Link;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CriteriaBundle\Entity\Criteria;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;
use Victoire\Bundle\WidgetBundle\Entity\Traits\LinkTrait;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;
use Victoire\Bundle\WidgetBundle\Repository\WidgetRepository;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

/**
 * WidgetDataWarmer.
 *
 * This class prepare all widgets with their associated entities for the current View
 * to reduce queries during page rendering.
 * Only OneToMany and ManyToOne associations are handled because no OneToOne or ManyToMany
 * associations have been used in Widgets.
 *
 * Ref: victoire_widget_map.widget_data_warmer
 */
class WidgetDataWarmer
{
    /* @var $em EntityManager */
    protected $em;
    protected $viewReferenceRepository;
    protected $widgetHelper;
    protected $accessor;
    protected $manyToOneAssociations;
    /**
     * @var BusinessEntityResolver
     */
    private $businessEntityResolver;

    /**
     * Constructor.
     *
     * @param ViewReferenceRepository $viewReferenceRepository
     * @param WidgetHelper            $widgetHelper
     * @param BusinessEntityResolver  $businessEntityResolver
     */
    public function __construct(
        ViewReferenceRepository $viewReferenceRepository,
        WidgetHelper $widgetHelper,
        BusinessEntityResolver $businessEntityResolver
    ) {
        $this->viewReferenceRepository = $viewReferenceRepository;
        $this->widgetHelper = $widgetHelper;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->businessEntityResolver = $businessEntityResolver;
    }

    /**
     * Find all Widgets for current View, inject them in WidgetMap and warm associated entities.
     *
     * @param EntityManager $em
     * @param View          $view
     */
    public function warm(EntityManager $em, View $view)
    {
        $this->em = $em;

        /* @var WidgetRepository $widgetRepo */
        $widgetRepo = $this->em->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget');
        $viewWidgets = $widgetRepo->findAllWidgetsForView($view);

        $this->injectWidgets($view, $viewWidgets);

        $this->extractAssociatedEntities($viewWidgets);
    }

    /**
     * Inject Widgets in View's builtWidgetMap.
     *
     * @param View $view
     * @param $viewWidgets
     */
    private function injectWidgets(View $view, $viewWidgets)
    {
        $builtWidgetMap = $view->getBuiltWidgetMap();

        foreach ($builtWidgetMap as $slot => $widgetMaps) {
            foreach ($widgetMaps as $i => $widgetMap) {
                foreach ($viewWidgets as $widget) {
                    if ($widget->getWidgetMap() == $widgetMap) {
                        $builtWidgetMap[$slot][$i]->addWidget($widget);

                        //Override Collection default behaviour to avoid useless query
                        $builtWidgetMap[$slot][$i]->getWidgets()->setDirty(false);
                        $builtWidgetMap[$slot][$i]->getWidgets()->setInitialized(true);
                        continue;
                    }
                }
            }
        }

        $view->setBuiltWidgetMap($builtWidgetMap);
    }

    /**
     * Pass through all widgets and associated entities to extract all missing associations,
     * store it by repository to group queries by entity type.
     *
     * @param Widget[] $entities Widgets and associated entities
     */
    private function extractAssociatedEntities(array $entities)
    {
        $linkIds = $associatedEntities = [];

        foreach ($entities as $entity) {
            $reflect = new \ReflectionClass($entity);

            //If Widget is already in cache, extract only its Criterias (used outside Widget rendering)
            $widgetCached = ($entity instanceof Widget && $this->widgetHelper->isCacheEnabled($entity));

            //If Widget has LinkTrait, store the entity link id
            if (!$widgetCached && $this->hasLinkTrait($reflect) && $entity->getLink()) {
                $linkIds[] = $entity->getLink()->getId();
            }

            //Pass through all entity associations
            $metaData = $this->em->getClassMetadata(get_class($entity));
            foreach ($metaData->getAssociationMappings() as $association) {
                $targetClass = $association['targetEntity'];

                //Skip already set WidgetMap association
                if ($targetClass == WidgetMap::class) {
                    continue;
                }

                //If Widget has OneToOne or ManyToOne association, store target entity id to construct
                //a single query for this entity type
                if ($metaData->isSingleValuedAssociation($association['fieldName'])
                    && !$widgetCached
                ) {
                    //If target Entity is not null, treat it
                    if ($targetEntity = $this->accessor->getValue($entity, $association['fieldName'])) {
                        $associatedEntities[$targetClass]['id'][] = new AssociatedEntityToWarm(
                            AssociatedEntityToWarm::TYPE_MANY_TO_ONE,
                            $entity,
                            $association['fieldName'],
                            $targetEntity->getId()
                        );
                    }
                }

                //If Widget has OneToMany association, store owner entity id and mappedBy value
                //to construct a single query for this entity type
                elseif ($metaData->isCollectionValuedAssociation($association['fieldName'])) {

                    //Even if Widget is cached, we need its Criterias used before cache call
                    if (!$widgetCached || $targetClass == Criteria::class) {

                        //If Collection is not null, treat it
                        if ($this->accessor->getValue($entity, $association['fieldName'])) {

                            //Don't use Collection getter directly and override Collection
                            //default behaviour to avoid useless query
                            $getter = 'get'.ucwords($association['fieldName']);
                            $entity->$getter()->setDirty(false);
                            $entity->$getter()->setInitialized(true);

                            $associatedEntities[$targetClass][$association['mappedBy']][] = new AssociatedEntityToWarm(
                                AssociatedEntityToWarm::TYPE_ONE_TO_MANY,
                                $entity,
                                $association['fieldName'],
                                $entity->getId()
                            );
                        }
                    }
                }
            }
        }

        $newEntities = $this->setAssociatedEntities($associatedEntities);
        $this->setPagesForLinks($linkIds);

        //Recursive call if previous has return new entities to warm
        if ($newEntities) {
            $this->extractAssociatedEntities($newEntities);
        }
    }

    /**
     * Set all missing associated entities.
     *
     * @param array $repositories
     *
     * @throws \Throwable
     * @throws \TypeError
     *
     * @return array
     */
    private function setAssociatedEntities(array $repositories)
    {
        $newEntities = [];

        foreach ($repositories as $repositoryName => $findMethods) {
            foreach ($findMethods as $findMethod => $associatedEntitiesToWarm) {

                //Extract ids to search
                $idsToSearch = array_map(function ($associatedEntityToWarm) {
                    return $associatedEntityToWarm->getEntityId();
                }, $associatedEntitiesToWarm);

                //Find by id for ManyToOne associations based on target entity id
                //Find by mappedBy value for OneToMany associations based on owner entity id
                $foundEntities = $this->em->getRepository($repositoryName)->findBy([
                    $findMethod => array_values($idsToSearch),
                ]);

                /* @var AssociatedEntityToWarm[] $associatedEntitiesToWarm */
                foreach ($associatedEntitiesToWarm as $associatedEntityToWarm) {
                    foreach ($foundEntities as $foundEntity) {
                        if ($associatedEntityToWarm->getType() == AssociatedEntityToWarm::TYPE_MANY_TO_ONE
                            && $foundEntity->getId() == $associatedEntityToWarm->getEntityId()
                        ) {
                            $inheritorEntity = $associatedEntityToWarm->getInheritorEntity();
                            $inheritorPropertyName = $associatedEntityToWarm->getInheritorPropertyName();
                            $this->accessor->setValue($inheritorEntity, $inheritorPropertyName, $foundEntity);
                            continue;
                        } elseif ($associatedEntityToWarm->getType() == AssociatedEntityToWarm::TYPE_ONE_TO_MANY
                            && $this->accessor->getValue($foundEntity, $findMethod) == $associatedEntityToWarm->getInheritorEntity()
                        ) {
                            $inheritorEntity = $associatedEntityToWarm->getInheritorEntity();
                            $inheritorPropertyName = $associatedEntityToWarm->getInheritorPropertyName();

                            //Don't use Collection getter directly and override Collection
                            //default behaviour to avoid useless query
                            $getter = 'get'.ucwords($inheritorPropertyName);
                            $inheritorEntity->$getter()->add($foundEntity);
                            $inheritorEntity->$getter()->setDirty(false);
                            $inheritorEntity->$getter()->setInitialized(true);

                            //Store new entities to warm if necessary
                            $newEntities[] = $foundEntity;
                            continue;
                        }
                    }
                }
            }
        }

        return $newEntities;
    }

    /**
     * Set viewReferencePage for each link.
     *
     * @param array $linkIds
     */
    private function setPagesForLinks(array $linkIds)
    {
        $viewReferences = [];

        /* @var Link[] $links */
        $links = $this->em->getRepository('VictoireCoreBundle:Link')->findById($linkIds);

        foreach ($links as $link) {
            if ($link->getParameters()['linkType'] == 'viewReference') {
                $viewReference = $this->viewReferenceRepository->getOneReferenceByParameters([
                    'id'     => $link->getParameters()['viewReference'],
                    'locale' => $link->getParameters()['locale'],
                ]);

                if ($viewReference instanceof ViewReference) {
                    $viewReferences[$link->getId()] = $viewReference;
                }
            }
        }

        /* @var Page[] $pages */
        $pages = $this->em->getRepository('VictoireCoreBundle:View')->findByViewReferences($viewReferences);

        foreach ($links as $link) {
            foreach ($pages as $page) {
                if (!($page instanceof BusinessTemplate) && $page->getReference() && $link->getViewReference() == $page->getReference()->getId()) {
                    $link->setViewReferencePage($page);
                }
            }
        }
    }

    /**
     * Check if reflection class has LinkTrait.
     *
     * @param \ReflectionClass $reflect
     *
     * @return bool
     */
    private function hasLinkTrait(\ReflectionClass $reflect)
    {
        $traits = $reflect->getTraits();
        foreach ($traits as $trait) {
            if ($trait->getName() == LinkTrait::class) {
                return true;
            }
        }

        if ($parentClass = $reflect->getParentClass()) {
            if ($this->hasLinkTrait($parentClass)) {
                return true;
            }
        }

        return false;
    }
}
