<?php

namespace Victoire\Bundle\BusinessEntityBundle\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as DoctrineAnnotationDriver;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Victoire\Bundle\BusinessEntityBundle\Event\BusinessEntityAnnotationEvent;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\CoreBundle\Annotations\BusinessEntity;
use Victoire\Bundle\CoreBundle\Annotations\BusinessProperty;
use Victoire\Bundle\CoreBundle\Annotations\ReceiverProperty;
use Victoire\Bundle\WidgetBundle\Event\WidgetAnnotationEvent;

/**
 * Parse all files to get BusinessClasses
 **/
class AnnotationDriver extends DoctrineAnnotationDriver
{
    public $reader;
    protected $eventDispatcher;
    protected $paths;

    /**
     * construct
     * @param Reader $reader
     * @param EventDispatcher $eventDispatcher
     * @param array $paths The paths where to search about Entities
     */
    public function __construct(Reader $reader, EventDispatcher $eventDispatcher, $paths)
    {
        $this->reader = $reader;
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
            $classAnnotations = $this->reader->getClassAnnotations($class);

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

                /** @var BusinessEntity $annotationObj */
                $annotationObj = $classAnnotations['Victoire\Bundle\CoreBundle\Annotations\BusinessEntity'];
                $businessEntity = BusinessEntityHelper::createBusinessEntity(
                    $className,
                    $this->loadBusinessProperties($class)
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
                    $this->loadReceiverProperties($class)
                );

                //dispatch victoire.widget_annotation_load to save receiverProperties in cache
                $this->eventDispatcher->dispatch('victoire.widget_annotation_load', $event);
            }
        }
    }

    /**
     * load business properties from ReflectionClass
     *
     * @return Array
     **/
    public function loadBusinessProperties(\ReflectionClass $class)
    {
        $businessProperties = array();
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $annotations = $this->reader->getPropertyAnnotations($property);
            foreach ($annotations as $key => $annotationObj) {
                if ($annotationObj instanceof BusinessProperty && !in_array($class, $businessProperties)) {
                    if (!$annotations[$key]->getTypes()) {
                        $message = $class->name.':$'.$property->name.'" field';
                        throw AnnotationException::requiredError('type', 'BusinessProperty annotation', $message, 'array or string');
                    }
                    foreach ($annotations[$key]->getTypes() as $type) {
                        $businessProperties[$type][$property->name] = $property->name;
                    }
                }
            }
        }

        return $businessProperties;
    }

    /**
     * load receiver properties from ReflectionClass
     *
     * @return Array
     **/
    public function loadReceiverProperties(\ReflectionClass $class)
    {
        $receiverProperties = array();
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $annotations = $this->reader->getPropertyAnnotations($property);
            foreach ($annotations as $key => $annotationObj) {
                if ($annotationObj instanceof ReceiverProperty && !in_array($class, $receiverProperties)) {
                    if (!$annotations[$key]->getTypes()) {
                        $message = $class->name.':$'.$property->name.'" field';
                        throw AnnotationException::requiredError('type', 'BusinessProperty annotation', $message, 'array or string');
                    }
                    foreach ($annotations[$key]->getTypes() as $type) {
                        $receiverProperties[$class->name][$type][$property->name] = $property->name;
                    }
                }
            }
        }

        return $receiverProperties;
    }
}
