<?php

namespace Victoire\Bundle\WidgetMapBundle\Warmer;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Entity\Link;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;
use Victoire\Bundle\WidgetBundle\Repository\WidgetRepository;

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
    protected $reader;
    protected $viewReferenceRepository;
    protected $widgetHelper;
    protected $em;
    protected $accessor;
    protected $forbiddenManyToOne;

    /**
     * Constructor.
     *
     * @param Reader                  $reader
     * @param ViewReferenceRepository $viewReferenceRepository
     * @param WidgetHelper            $widgetHelper
     * @param array                   $forbiddenManyToOne
     */
    public function __construct(
        Reader $reader,
        ViewReferenceRepository $viewReferenceRepository,
        WidgetHelper $widgetHelper,
        array $forbiddenManyToOne
    ) {
        $this->reader = $reader;
        $this->viewReferenceRepository = $viewReferenceRepository;
        $this->widgetHelper = $widgetHelper;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->forbiddenManyToOne = $forbiddenManyToOne;
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
                        continue 2;
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

            //Pass through all properties annotations
            $properties = $this->getAvailableProperties($reflect);
            foreach ($properties as $property) {
                $annotations = $this->reader->getPropertyAnnotations($property);
                foreach ($annotations as $key => $annotationObj) {

                    //If Widget has ManyToOne association, store target entity id to construct
                    //a single query for this entity type
                    if ($annotationObj instanceof ManyToOne
                        && !$this->isForbiddenManyToOne($reflect->getShortName(), $annotationObj->targetEntity)
                        && !$widgetCached
                    ) {
                        //If target Entity is not null, treat it
                        if ($targetEntity = $this->accessor->getValue($entity, $property->getName())) {
                            $targetClass = $this->resolveNamespace($reflect, $annotationObj->targetEntity);
                            $associatedEntities[$targetClass]['id'][] = new AssociatedEntityToWarm(
                                AssociatedEntityToWarm::TYPE_MANY_TO_ONE,
                                $entity,
                                $property->getName(),
                                $targetEntity->getId()
                            );
                        }
                    }

                    //If Widget has OneToMany association, store owner entity id and mappedBy value
                    //to construct a single query for this entity type
                    elseif ($annotationObj instanceof OneToMany) {
                        $targetClass = $this->resolveNamespace($reflect, $annotationObj->targetEntity);

                        if (!$widgetCached || $targetClass == '\Victoire\Bundle\CriteriaBundle\Entity\Criteria') {
                            //If Collection is not null, treat it
                            if ($this->accessor->getValue($entity, $property->getName())) {

                                //Override Collection default behaviour to avoid useless query
                                $getter = 'get'.ucwords($property->getName());
                                $entity->$getter()->setDirty(false);
                                $entity->$getter()->setInitialized(true);

                                $associatedEntities[$targetClass][$annotationObj->mappedBy][] = new AssociatedEntityToWarm(
                                    AssociatedEntityToWarm::TYPE_ONE_TO_MANY,
                                    $entity,
                                    $property->getName(),
                                    $entity->getId()
                                );
                            }
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

                //Find by id for ManyToOne associations based on target entity id
                //Find by mappedBy value for OneToMany associations based on owner entity id
                $idsToSearch = $this->extractAssociatedEntitiesIds($associatedEntitiesToWarm);
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

                            //Override Collection default behaviour to avoid useless query
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
                    'id' => $link->getParameters()['viewReference'],
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
        $linkTraitName = 'Victoire\Bundle\WidgetBundle\Entity\Traits\LinkTrait';

        $traits = $reflect->getTraits();
        foreach ($traits as $trait) {
            if ($trait->getName() == $linkTraitName) {
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

    /**
     * Extract entities ids from an array of AssociatedEntityToWarm.
     *
     * @param AssociatedEntityToWarm[] $associatedEntitiesToWarm
     *
     * @return array
     */
    private function extractAssociatedEntitiesIds(array $associatedEntitiesToWarm)
    {
        $extractedIds = [];
        foreach ($associatedEntitiesToWarm as $associatedEntityToWarm) {
            $extractedIds[] = $associatedEntityToWarm->getEntityId();
        }

        return $extractedIds;
    }

    /**
     * Some ManyToOne associations are forbidden because they are treated in inverse side
     * with OneToMany associations.
     *
     * @param string $annotationEntity
     * @param string $annotationTarget
     *
     * @return bool
     */
    private function isForbiddenManyToOne($annotationEntity, $annotationTarget)
    {
        //Get only class name
        $annotationTarget = explode('\\', $annotationTarget);
        $annotationTarget = end($annotationTarget);

        foreach ($this->forbiddenManyToOne as $entity => $target) {
            if ($entity == $annotationEntity && $target == $annotationTarget) {
                return true;
            }
        }

        return false;
    }

    /**
     * Targe entities namespaces are not always fully defined.
     * This method resolve namespace by concatenate class and parent class namespaces.
     *
     * @param \ReflectionClass $reflect
     * @param $targetEntity
     *
     * @return mixed
     */
    private function resolveNamespace(\ReflectionClass $reflect, $targetEntity)
    {
        if (class_exists($targetEntity)) {
            return $targetEntity;
        }

        $composedNamespace = sprintf(
            '%s\%s',
            $reflect->getNamespaceName(),
            $targetEntity
        );

        if (class_exists($composedNamespace)) {
            return $composedNamespace;
        }

        return $this->resolveNamespace($reflect->getParentClass(), $targetEntity);
    }

    /**
     * Avoid passing through all Widget properties.
     * Only property "criterias" is used for this class.
     *
     * @param \ReflectionClass $reflect
     *
     * @return array
     */
    private function getAvailableProperties(\ReflectionClass $reflect)
    {
        return array_filter($reflect->getProperties(), function ($prop) {
            if ($prop->getDeclaringClass()->getName() == 'Victoire\Bundle\WidgetBundle\Entity\Widget'
                && $prop->getName() != 'criterias'
            ) {
                return false;
            }

            return true;
        });
    }
}
