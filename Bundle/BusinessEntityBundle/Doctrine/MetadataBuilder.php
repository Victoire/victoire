<?php

namespace Victoire\Bundle\BusinessEntityBundle\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Victoire\Bundle\BusinessEntityBundle\Event\BusinessEntityAnnotationEvent;
use Victoire\Bundle\CoreBundle\Annotations\BusinessEntity;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Event\WidgetAnnotationEvent;

/**
 * The business entities metadata builder
 *
 * ref: victoire_business_entity.metadata_builder
 **/
class MetadataBuilder
{
    /** BusinessEntity helper instance
     */
    protected $helper;

    /** Annotation driver instance
     */
    protected $driver;

    /**
     * business entities valid paths
     */
    protected $paths;

    /**
     * construct
     * @param BusinessEntityHelper     $helper
     * @param EventDispatcherInterface $eventDispatcher
     * @param array                    $paths           The paths where to search about Entities
     */
    public function __construct(AnnotationDriver $driver, BusinessEntityHelper $helper, EventDispatcherInterface $eventDispatcher, $paths)
    {
        $this->driver = $driver;
        $this->helper = $helper;
        $this->eventDispatcher = $eventDispatcher;
        $this->paths = $paths;
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        /* @var $metadata \Doctrine\ORM\Mapping\ClassMetadataInfo */
        $class = $metadata->getReflectionClass();

        if (! $class) {
            $class = new \ReflectionClass($metadata->name);
        }

        $classPath = dirname($class->getFileName());
        $inPaths = false;

        foreach ($this->paths as $key => $_path) {
            //Check the entity path is in watching paths
            if (strpos($classPath, realpath($_path)) === 0) {
                $inPaths = true;
            }
        }

        if ($inPaths) {
            $classAnnotations = $this->driver->reader->getClassAnnotations($class);

            if ($classAnnotations) {
                foreach ($classAnnotations as $key => $annot) {
                    if (! is_numeric($key)) {
                        continue;
                    }

                    $classAnnotations[get_class($annot)] = $annot;
                }
            }

            // Evaluate Entity annotation
            if (isset($classAnnotations['Victoire\Bundle\CoreBundle\Annotations\BusinessEntity'])) {
                $annotationObj = $classAnnotations['Victoire\Bundle\CoreBundle\Annotations\BusinessEntity'];
                $businessEntity = $this->helper->createBusinessEntity(
                    $className,
                    $this->driver->loadBusinessProperties($class)
                );

                $event = new BusinessEntityAnnotationEvent(
                    $businessEntity,
                    $annotationObj->getWidgets()
                );

                //do what you want (caching BusinessEntity...)
                $this->eventDispatcher->dispatch('victoire.business_entity_annotation_load', $event);
            }

            //check if the entity is a widget (extends (in depth) widget class)
            $parentClass = $class->getParentClass();
            $isWidget = false;
            while ($parentClass && ($parentClass = $parentClass->getParentClass()) && !$isWidget && $parentClass->name != null) {
                $isWidget = $parentClass->name === 'Victoire\\Bundle\\WidgetBundle\\Model\\Widget';
            }

            if ($isWidget) {
                $event = new WidgetAnnotationEvent(
                    $className,
                    $this->driver->loadReceiverProperties($class)
                );

                //dispatch victoire.widget_annotation_load to save receiverProperties in cache
                $this->eventDispatcher->dispatch('victoire.widget_annotation_load', $event);
            }
        }
    }
}
