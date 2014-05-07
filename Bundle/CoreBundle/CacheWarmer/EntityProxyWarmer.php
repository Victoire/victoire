<?php
namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Victoire\Bundle\CoreBundle\CacheWarmer\GeneratedClassLoader;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\ConfigCache;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;

class EntityProxyWarmer extends CacheWarmer
{

    private $annotationReader;

    public function __construct($annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }
    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
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


    public function isOptional()
    {
        return false;
    }
}
