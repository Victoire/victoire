<?php

namespace Victoire\Bundle\BusinessEntityBundle\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as DoctrineAnnotationDriver;
use Doctrine\ORM\Mapping\MappingException;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
    protected $regex;
    protected $logger;

    /**
     * construct.
     *
     * @param AnnotationReader         $reader
     * @param EventDispatcherInterface $eventDispatcher
     * @param WidgetHelper             $widgetHelper
     * @param array                    $paths           The paths where to search about Entities
     * @param string                   $regex
     * @param LoggerInterface          $logger
     */
    public function __construct(
        Reader $reader,
        EventDispatcherInterface $eventDispatcher,
        $widgetHelper,
        $paths,
        $regex,
        LoggerInterface $logger
    ) {

        parent::__construct($reader, $paths);
        $this->reader = $reader;
        $this->eventDispatcher = $eventDispatcher;
        $this->widgetHelper = $widgetHelper;
        $this->paths = $paths;
    }

    /**
     * Get all class names.
     *
     * @throws MappingException
     *
     * @return array
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
                $this->logger->warning(sprintf(
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
                $this->regex,
                \RecursiveRegexIterator::GET_MATCH
            );
            foreach ($iterator as $file) {
                $sourceFile = realpath($file[0]);
                $includedFiles[] = $sourceFile;
            }
        }

        foreach ($includedFiles as $fileName) {
            $class = $this->getClassNameFromFile($fileName);
            if (class_exists($class) && !$this->isTransient($class)) {
                $classes[] = $class;
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
}
