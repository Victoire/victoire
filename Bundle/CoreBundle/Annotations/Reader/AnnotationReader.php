<?php
namespace Victoire\Bundle\CoreBundle\Annotations\Reader;

use Victoire\Bundle\CoreBundle\Annotations\BusinessEntity;
use Victoire\Bundle\CoreBundle\Annotations\BusinessProperty;
use Victoire\Bundle\CoreBundle\Annotations\ReceiverProperty;
use Victoire\Bundle\CoreBundle\Entity\Widget;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationException;

use Doctrine\Common\Cache\Cache,
    Doctrine\Common\Cache\ArrayCache,
    Doctrine\Common\Annotations\CachedReader;

/**
 * The annotation reader for the business entities
 *
 * ref: victoire_core.annotation_reader
 **/
class AnnotationReader extends AnnotationDriver
{

    private $cache;
    private $widgets;
    private $widgetManager;

    /**
     * construct
     *
     * @param unknown $reader
     * @param unknown $paths
     * @param unknown $widgets
     */
    public function __construct($reader, $widgetManager, $paths, $widgets)
    {
        $this->widgets = $widgets;
        $this->reader = $reader;
        $this->widgetManager = $widgetManager;
        if ($paths) {
            $this->addPaths(array($paths."/../"));
        }
    }

    /**
     * Set the cache
     *
     * @param unknown $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * this method get annotated business classes (from cache if enabled)
     *
     * @return array $businessClasses
     **/
    public function getBusinessClasses()
    {
        if (!$businessClasses = $this->cache->fetch('victoire_core_business_classes')) {
            $classes = $this->getAllClassnames();
            $businessClasses = $this->loadBusinessClasses($classes);
            $this->cache->save('victoire_core_business_classes', $businessClasses);
        }

        return $businessClasses;
    }
    /**
     * this method get annotated business classes (from cache if enabled)
     *
     * @return array $businessClasses
     **/
    public function getBusinessClassesForWidget(Widget $widget)
    {
        $widgetName = $this->widgetManager->getManager($widget)->getWidgetName();

        $businessClassesForWidget = $this->cache->fetch('victoire_core_business_classes_for_widget');
        if (!$businessClassesForWidget || (is_array($businessClassesForWidget) && !array_key_exists($widgetName, $businessClassesForWidget))) {
            $businessClasses = $this->getBusinessClasses();
            /* TODO : We shouldn't parse THREE times each class.
             * Now, we do in 3 times
             * - First  : we parse it to know if it's a business entity
             * - Second : if it's a "list" widget aware
             * - Third  : we check if there is all the required receiver properties
             * We should do this in a single call
             **/
            $businessClassesForWidget[$widgetName] = $this->loadBusinessClassesForWidget($businessClasses, $widgetName);
            $this->cache->save('victoire_core_business_classes_for_widget', $businessClassesForWidget);
        }

        return $businessClassesForWidget[$widgetName];
    }
    /**
     * this method get annotated business classes (from cache if enabled)
     *
     * @return array $businessClasses
     **/
    public function getBusinessProperties($class)
    {
        $rc = new \ReflectionClass($class);
        $businessProperties = $this->cache->fetch('victoire_core_business_properties');
        if (!$businessProperties || (is_array($businessProperties) && !array_key_exists($class, $businessProperties))) {
            $businessProperties[$class] = $this->loadBusinessProperties($rc);
            $this->cache->save('victoire_core_business_properties', $businessProperties);
        }

        /* TODO REMOVE THIS : It exists to avoid a crash but should be prevent before */

        $businessProperties[$class] = array_merge(
            array(
                'textable' => array(),
                'mediable'  => array(),
                'datable'   => array(),
            ),
            $businessProperties[$class]
        );

        return $businessProperties[$class];
    }
    /**
     * this method get annotated receiver widget properties (from cache if enabled)
     *
     * @return array $receiverProperties
     **/
    public function getReceiverProperties()
    {
        $receiverProperties = $this->cache->fetch('victoire_core_receiver_properties');
        if (!$receiverProperties || (is_array($receiverProperties) )) {
            $receiverProperties = $this->loadReceiverProperties();
            $this->cache->save('victoire_core_receiver_properties', $receiverProperties);
        }

        return $receiverProperties;
    }

    /**
     * Get annotated business classes
     *
     * @param  array  $classes    All business entities class names
     * @param  string $widgetName The widget's class name we're looking entities
     * @return array  $businessClasses
     */
    private function loadBusinessClassesForWidget($classes, $widgetName)
    {
        $businessClassesForWidget = array();
        foreach ($classes as $key => $classNamespace) {
            $annotations = $this->reader->getClassAnnotations(new \ReflectionClass($classNamespace));
            $className = explode('\\', $classNamespace);
            $className = strtolower(array_pop($className));
            foreach ($annotations as $key => $annotationObj) {

                if ($annotationObj instanceof BusinessEntity) {
                    if ($annotationObj->getWidgets() !== null) {
                        foreach ($annotationObj->getWidgets() as $availableWidget) {
                            if ($availableWidget === $widgetName && !in_array($className, $businessClassesForWidget)) {
                                $businessClassesForWidget[$className] = $classNamespace;
                            }
                        }
                    }
                }
            }

        }

        return $businessClassesForWidget;
    }

    /**
     * Return all business classes by seeking BusinessEntity anotations
     * @param  array $classes Classnames of all entities of your app (including vendors)
     * @return array All the business classes of your app
     */
    private function loadBusinessClasses($classes)
    {
        $businessClasses = array();

        foreach ($classes as $className) {
            //Parse all classes for BusinessEntity annotation
            $annotations = $this->reader->getClassAnnotations(new \ReflectionClass($className));
            foreach ($annotations as $key => $annotationObj) {

                if ($annotationObj instanceof BusinessEntity) {
                    if (!in_array($className, $businessClasses)) {
                        $entityName = explode('\\', $className);
                        $businessClasses[strtolower(array_pop($entityName))] = $className;
                    }
                }
            }
        }

        return $businessClasses;

    }

    private function loadBusinessProperties(\ReflectionClass $class)
    {
        $businessProperties = array();
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $annotations = $this->reader->getPropertyAnnotations($property);
            foreach ($annotations as $key => $annotationObj) {
                if ($annotationObj instanceof BusinessProperty && !in_array($class, $businessProperties)) {
                    if (!$annotations[$key]->getTypes()) {
                        $message = $class->name . ':$' . $property->name . '" field';
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
    private function loadReceiverProperties()
    {
        $receiverProperties = array();
        foreach ($this->widgets as $widget) {
            $class = new \ReflectionClass($widget['class']);

            $properties = $class->getProperties();
            foreach ($properties as $property) {
                $annotations = $this->reader->getPropertyAnnotations($property);
                foreach ($annotations as $key => $annotationObj) {
                    if ($annotationObj instanceof ReceiverProperty && !in_array($class, $receiverProperties)) {
                        if (!$annotations[$key]->getTypes()) {
                            $message = $class->name . ':$' . $property->name . '" field';
                            throw AnnotationException::requiredError('type', 'BusinessProperty annotation', $message, 'array or string');
                        }
                        foreach ($annotations[$key]->getTypes() as $type) {
                            $receiverProperties[$widget['name']][$type][$property->name] = $property->name;
                        }
                    }
                }
            }
        }

        return $receiverProperties;

    }

    /**
     * Returns all entities of your application including vendors
     * @return array All entities classnames
     */
    public function getAllClassnames()
    {
        if ($this->classNames !== null) {
            return $this->classNames;
        }

        if (!$this->paths) {
            throw MappingException::pathRequired();
        }

        $classes = array();
        $includedFiles = array();

        foreach ($this->paths as $path) {
            if ( ! is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            $iterator = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ),
                '/^.+\/(src|vendor\/victoire)\/.+\/Entity\/.+' . str_replace('.', '\.', $this->fileExtension) . '$/i',
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

}
