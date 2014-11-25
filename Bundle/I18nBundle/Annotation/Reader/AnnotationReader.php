<?php
namespace Victoire\Bundle\I18nBundle\Annotations\Reader;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\MappingException;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\CachedReader;

/**
 * The annotation reader for the business entities
 *
 * ref: victoire_core.annotation_reader
 **/
class AnnotationReader extends AnnotationDriver
{

    private $cache;
    private $widgets;
    private $widgetHelper;

    /**
     * construct
     * @param unknown      $reader
     * @param unknown      $paths
     */
    public function __construct($reader, $paths)
    {
        $this->reader = $reader;
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
