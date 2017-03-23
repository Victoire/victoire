<?php

namespace Victoire\Bundle\BusinessEntityBundle\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as DoctrineAnnotationDriver;
use Doctrine\ORM\Mapping\MappingException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Victoire\Bundle\BusinessEntityBundle\Entity\ReceiverProperty;
use Victoire\Bundle\CoreBundle\Annotations\ReceiverProperty as ReceiverPropertyAnnotation;
use Victoire\Bundle\WidgetBundle\Event\WidgetAnnotationEvent;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;

/**
 * Parse all files to get BusinessClasses.
 **/
class AnnotationDriver extends DoctrineAnnotationDriver
{
    public $reader;
    protected $eventDispatcher;
    protected $widgetHelper;
    protected $paths;
    protected $logger;

    /**
     * construct.
     *
     * @param Reader                   $reader
     * @param EventDispatcherInterface $eventDispatcher
     * @param WidgetHelper             $widgetHelper
     * @param array                    $paths           The paths where to search about Entities
     * @param LoggerInterface          $logger
     */
    public function __construct(
        Reader $reader,
        EventDispatcherInterface $eventDispatcher,
        $widgetHelper,
        $paths,
        LoggerInterface $logger
    ) {
        $this->reader = $reader;
        $this->eventDispatcher = $eventDispatcher;
        $this->widgetHelper = $widgetHelper;
        $this->paths = $paths;
        $this->logger = $logger;
    }

    /**
     * Get all class names.
     *
     * @return array
     *
     * @throws MappingException
     */
    public function getAllClassNames()
    {
        if (!$this->paths) {
            throw MappingException::pathRequired();
        }
        $classes = [];
        $includedFiles = [];
        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                $this->logger->error(sprintf(
                    'The given path "%s" seems to be incorrect. You need to edit victoire_core.base_paths configuration.',
                    $path
                ));
                continue;
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
     * Parse the given Class to find some annotations related to BusinessEntities.
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

            //check if the entity is a widget (extends (in depth) widget class)
            $parentClass = $class->getParentClass();
            $isWidget = false;
            while ($parentClass && ($parentClass = $parentClass->getParentClass()) && !$isWidget && $parentClass->name != null) {
                $isWidget = $parentClass->name === 'Victoire\\Bundle\\WidgetBundle\\Model\\Widget';
            }
            if ($isWidget) {
                if ($this->widgetHelper->isEnabled(new $class->name())) {
                    $event = new WidgetAnnotationEvent(
                        $this->widgetHelper->getWidgetName(new $class->name()),
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
     * Load receiver properties and NotBlank constraints from ReflectionClass.
     *
     * @param \ReflectionClass $class
     *
     * @throws AnnotationException
     *
     * @return array
     */
    protected function loadReceiverProperties(\ReflectionClass $class)
    {
        $receiverPropertiesTypes = [];
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
                    } elseif ($annotationObj instanceof NotBlank) {
                        throw new Exception(sprintf(
                            'Property "%s" in class "%s" has a @ReceiverProperty annotation and by consequence can not use NotBlank annotation',
                            $refProperty->name,
                            $refProperty->class
                        ));
                    } elseif ($annotationObj instanceof NotNull) {
                        throw new Exception(sprintf(
                            'Property "%s" in class "%s" has a @ReceiverProperty annotation and by consequence can not use NotNull annotation',
                            $refProperty->name,
                            $refProperty->class
                        ));
                    } elseif ($annotationObj instanceof ReceiverPropertyAnnotation && $annotationObj->isRequired()) {
                        $receiverProperty->setRequired(true);
                    }
                }
            }
        }

        return $receiverPropertiesTypes;
    }
}
