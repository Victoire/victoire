<?php

namespace Victoire\Bundle\BusinessEntityBundle\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as DoctrineAnnotationDriver;
use Doctrine\ORM\Mapping\MappingException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Victoire\Bundle\BusinessEntityBundle\Entity\ReceiverProperty;
use Victoire\Bundle\BusinessEntityBundle\Event\BusinessEntityAnnotationEvent;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\CoreBundle\Annotations\BusinessEntity;
use Victoire\Bundle\CoreBundle\Annotations\BusinessProperty;
use Victoire\Bundle\CoreBundle\Annotations\ReceiverProperty as ReceiverPropertyAnnotation;
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
            if (!is_dir($path)) {
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
            if (in_array($sourceFile, $includedFiles) && !$this->isTransient($className)) {
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

            if (!empty($classAnnotations)) {
                foreach ($classAnnotations as $key => $annot) {
                    if (!is_numeric($key)) {
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
                if ($this->widgetHelper->isEnabled(new $class->name)) {
                    $event = new WidgetAnnotationEvent(
                        $this->widgetHelper->getWidgetName(new $class->name),
                        $this->loadReceiverProperties($class)
                    );

                    //dispatch victoire.widget_annotation_load to save receiverProperties in cache
                    $this->eventDispatcher->dispatch('victoire.widget_annotation_load', $event);
                } else {
                    error_log(sprintf('Widget name not found for widget %s. Is this widget declared in AppKernel ?', $class->name));
                }
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
        // we load business properties of parents recursively
        // because they are defined by an annotation not by the property type(private, protected, public)
        $parentClass = $class->getParentClass();
        if ($parentClass) {
            //load parent properties recursively
            $parentProperties = $this->loadBusinessProperties(new \ReflectionClass($parentClass->getName()));
            foreach ($parentProperties as $key => $parentProperty) {
                if (array_key_exists($key, $businessProperties)) {
                    //if parent and current have a same business property type we merge the properties and remove
                    //duplicates if properties are the same;
                    $businessProperties[$key] = array_unique(array_merge($parentProperty, $businessProperties[$key]));
                } else {
                    //else we had a business property type for the parent properties
                    $businessProperties[$key] = $parentProperty;
                }
            }
        }

        return $businessProperties;
    }

    /**
     * Load receiver properties and NotBlank constraints from ReflectionClass
     *
     * @param \ReflectionClass $class
     * @return array
     * @throws AnnotationException
     */
    protected function loadReceiverProperties(\ReflectionClass $class)
    {

        $receiverPropertiesTypes = array();
        $properties = $class->getProperties();

        //Store receiver properties
        foreach ($properties as $property) {
            $annotations = $this->reader->getPropertyAnnotations($property);
            foreach ($annotations as $key => $annotationObj) {
                if ($annotationObj instanceof ReceiverPropertyAnnotation && !in_array($class, $receiverPropertiesTypes)) {
                    if (!$annotations[$key]->getTypes()) {
                        $message = $class->name.':$'.$property->name.'" field';
                        throw AnnotationException::requiredError('type', 'ReceiverProperty annotation', $message, 'array or string');
                    }
                    foreach ($annotations[$key]->getTypes() as $type) {
                        $receiverProperty = new ReceiverProperty();
                        $receiverProperty->setFieldName($property->name);
                        $receiverPropertiesTypes[$type][] = $receiverProperty;
                    }
                }
            }
        }

        //Set receiver properties as required if necessary
        foreach ($receiverPropertiesTypes as $type => $receiverProperties) {
            /* @var ReceiverProperty[] $receiverProperties */
            foreach ($receiverProperties as $receiverProperty) {
                $receiverPropertyName = $receiverProperty->getFieldName();
                $refProperty = $class->getProperty($receiverPropertyName);
                $annotations = $this->reader->getPropertyAnnotations($refProperty);

                    foreach ($annotations as $key => $annotationObj) {

                        if ($annotationObj instanceof Column && $annotationObj->nullable === false) {
                            throw new Exception(sprintf(
                                'Property "%s" in class "%s" has a @ReceiverProperty annotation and by consequence must have "nullable=true" for ORM\Column annotation',
                                $refProperty->name,
                                $refProperty->class
                            ));
                        } else if($annotationObj instanceof NotBlank) {
                            throw new Exception(sprintf(
                                'Property "%s" in class "%s" has a @ReceiverProperty annotation and by consequence can not use NotBlank annotation',
                                $refProperty->name,
                                $refProperty->class
                            ));
                        } else if($annotationObj instanceof NotNull) {
                            throw new Exception(sprintf(
                                'Property "%s" in class "%s" has a @ReceiverProperty annotation and by consequence can not use NotNull annotation',
                                $refProperty->name,
                                $refProperty->class
                            ));
                        } else if ($annotationObj instanceof ReceiverPropertyAnnotation && $annotationObj->isRequired()) {
                            $receiverProperty->setRequired(true);
                        }

                    }

            }
        }

        return $receiverPropertiesTypes;
    }
}
