<?php

namespace Victoire\Bundle\BusinessEntityBundle\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as DoctrineAnnotationDriver;
use Doctrine\ORM\Mapping\MappingException;
use Metadata\Driver\DriverInterface;
use Metadata\MergeableClassMetadata;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Victoire\Bundle\BusinessEntityBundle\Event\BusinessEntityAnnotationEvent;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\CoreBundle\Annotations\BusinessEntity;
use Victoire\Bundle\CoreBundle\Annotations\BusinessProperty;
use Victoire\Bundle\CoreBundle\Annotations\ReceiverProperty;
use Victoire\Bundle\WidgetBundle\Event\WidgetAnnotationEvent;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;

/**
 * Parse all files to get BusinessClasses
 *
 **/
class AnnotationDriver extends DoctrineAnnotationDriver
{
    public $reader;
    protected $eventDispatcher;
    protected $widgetHelper;
    protected $paths;

    /**
     * construct
     * @param Reader                   $reader
     * @param EventDispatcherInterface $eventDispatcher
     * @param WidgetHelper             $widgetHelper
     * @param array                    $paths The paths where to search about Entities
     */
    public function __construct(Reader $reader, EventDispatcherInterface $eventDispatcher, $widgetHelper, $paths)
    {
        $this->reader = $reader;
        $this->eventDispatcher = $eventDispatcher;
        $this->widgetHelper = $widgetHelper;
        $this->paths = $paths;
    }

    /**
     * Get all class names
     *
     * @return string[]
     */
    public function getAllClassNames()
    {
        if (!$this->paths) {
            throw MappingException::pathRequired();
        }
        $classes = array();
        $includedFiles = array();
        foreach ($this->paths as $path) {
            if (! is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }
            $iterator = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ),
                '/^.+\/Entity\/.+\.php$/i',
                \RecursiveRegexIterator::GET_MATCH
            );
            foreach ($iterator as $file) {
                $sourceFile = realpath($file[0]);
                require_once $sourceFile;
                $includedFiles[] = $sourceFile;
            }
        }
        $declared = get_declared_classes();
        foreach ($declared as $className) {
            $rc = new \ReflectionClass($className);
            $sourceFile = $rc->getFileName();
            if (in_array($sourceFile, $includedFiles) && ! $this->isTransient($className)) {
                $classes[] = $className;
            }
        }

        return $classes;
    }

    /**
     * Parse the given Class to find some annotations related to BusinessEntities
     */
    public function parse(\ReflectionClass $class)
    {
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
                    $class->getName(),
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
                    $this->widgetHelper->getWidgetName(new $class->name),
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
    protected function loadBusinessProperties(\ReflectionClass $class)
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
                        $businessProperties[$type][] = $property->name;
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
    protected function loadReceiverProperties(\ReflectionClass $class)
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
                        $receiverProperties[$type][] = $property->name;
                    }
                }
            }
        }

        return $receiverProperties;
    }
}
