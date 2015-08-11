<?php
namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Victoire\Bundle\BusinessEntityBundle\Generator\EntityProxyGenerator;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;

/**
 * The EntityProxyWarmer uses the EntityProxyGenerator to generate the entity proxy class according to BusinessEntities.
 */
class EntityProxyWarmer extends CacheWarmer
{
    private $businessEntityHelper;
    private $fileLocator;

    /**
     * Constructor
     * @param BusinessEntityHelper $businessEntityHelper
     * @param FileLocator          $fileLocator
     *
     */
    public function __construct(BusinessEntityHelper $businessEntityHelper, FileLocator $fileLocator)
    {
        $this->businessEntityHelper = $businessEntityHelper;
        $this->fileLocator = $fileLocator;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     *
     * @throws \RuntimeException
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

        $generator = new EntityProxyGenerator($this->businessEntityHelper, $this->fileLocator);
        $cacheContent = $generator->generate();

        $this->writeCacheFile($file, $cacheContent);
        if (!class_exists("Victoire\\Bundle\\CoreBundle\\Entity\\EntityProxy")) {
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
