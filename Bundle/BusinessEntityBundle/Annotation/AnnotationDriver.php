<?php

namespace Victoire\Bundle\BusinessEntityBundle\Annotation;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\MappingException;

/**
 * Parse all files to get BusinessClasses
 **/
class AnnotationDriver
{
    /** Annotation reader instance
     */
    public $reader;

    /** Business class names
     */
    protected $classNames;

    /**
     * valid paths
     */
    protected $paths;

    /**
     * construct
     * @param AnnotationReader $reader
     * @param array            $paths  The paths where to search about Entities
     */
    public function __construct(Reader $reader, $paths)
    {
        $this->reader = $reader;
        $this->paths = $paths;
    }

    /**
     * get all business entities from annotation
     *
     * @return Array<BusinessEntity>
     **/
    public function getBusinessEntities()
    {
        foreach ($this->getAllClassnames() as $className) {
        }
    }

    /**
     * Returns all entities of your application including vendors
     * @return array All entities classnames
     */
    private function getAllClassnames()
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
            if (! is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            $iterator = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ),
                '/^.+\/(src|vendor\/victoire)\/.+\/Entity\/.+'.str_replace('.', '\.', $this->fileExtension).'$/i',
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
     * load business properties from ReflectionClass
     *
     * @return Array
     **/
    private function loadBusinessProperties(\ReflectionClass $class)
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
    private function loadReceiverProperties(\ReflectionClass $class)
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
