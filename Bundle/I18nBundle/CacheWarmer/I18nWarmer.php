<?php
namespace Victoire\Bundle\I18nBundle\CacheWarmer;

use Behat\Behat\Exception\Exception;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Victoire\Bundle\CoreBundle\Annotations\Reader\AnnotationReader;

/**
 *
 * @author Florian Raux
 *
 */
class I18nWarmer extends CacheWarmer
{
    private $annotationReader;
    protected $availableLocales;
    protected $fileLocator;

    /**
     * Constructor
     * @param AnnotationReader $annotationReader
     * @param array            $availableLocales Got from I18n config
     * @param FileLocator      $fileLocator
     */
    public function __construct(AnnotationReader $annotationReader, $availableLocales, FileLocator $fileLocator)
    {
        $this->annotationReader = $annotationReader;
        $this->applicationLocales = $availableLocales;
        $this->fileLocator = $fileLocator;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     *
     * @throws Exception
     *
     * this method Read the cache and if the entity does not exists it loads it
     */
    public function warmUp($cacheDir)
    {
        $dir = $cacheDir.'/victoire/Entity';
        $file = $dir.'/I18n.php';

        if (!file_exists($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create directory "%s".', $dir));
            }
        }

        $generator = new I18nGenerator($this->annotationReader, $this->applicationLocales, $this->fileLocator);
        $cacheContent = $generator->generate();

        $this->writeCacheFile($file, $cacheContent);
        if (!class_exists("Victoire\Bundle\I18nBundle\Entity\I18n")) {
            include_once $file;
        }
    }

    /**
     * IS the warmer optionnal
     *
     * @return boolean
     */
    public function isOptional()
    {
        return false;
    }
}
