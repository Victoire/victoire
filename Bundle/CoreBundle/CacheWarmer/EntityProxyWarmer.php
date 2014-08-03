<?php
namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Behat\Behat\Exception\Exception;

/**
 *
 * @author Paul Andrieux
 *
 */
class EntityProxyWarmer extends CacheWarmer
{
    private $annotationReader;

    /**
     * Constructor
     *
     * @param unknown $annotationReader
     */
    public function __construct($annotationReader)
    {
        $this->annotationReader = $annotationReader;
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

        $generator = new EntityProxyGenerator($this->annotationReader);
        $generator->setSkeletonDirs(__DIR__."/skeleton/");
        $cacheContent = $generator->generate();

        $this->writeCacheFile($file, $cacheContent);
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
