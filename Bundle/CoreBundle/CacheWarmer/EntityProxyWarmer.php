<?php
namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\Config\FileLocator;

/**
 *
 * @author Paul Andrieux
 *
 */
class EntityProxyWarmer extends CacheWarmer
{
    private $annotationReader;
    private $fileLocator;

    /**
     * Constructor
     *
     * @param unknown $annotationReader
     */
    public function __construct($annotationReader, FileLocator $fileLocator)
    {
        $this->annotationReader = $annotationReader;
        $this->fileLocator = $fileLocator;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     *
     * @throws Exception
     */
    public function warmUp($cacheDir)
    {
        $dir = $cacheDir.'/victoire/Entity';
        $file = $dir.'/EntityProxy.php';

        if (!file_exists($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create directory "%s".', $dir));
            }
        }

        $generator = new EntityProxyGenerator($this->annotationReader, $this->fileLocator);
        $cacheContent = $generator->generate();

        $this->writeCacheFile($file, $cacheContent);
        if (!class_exists("Victoire\Bundle\CoreBundle\Entity\EntityProxy")) {
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
