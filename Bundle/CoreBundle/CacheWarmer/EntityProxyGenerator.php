<?php
namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Victoire\Bundle\BusinessEntityBundle\Reader\BusinessEntityCacheReader;

/**
 *
 * @author Paul Andrieux
 *
 */
class EntityProxyGenerator extends Generator
{
    private $cacheReader;
    private $fileLocator;

    /**
     * Constructor
     * @param BusinessEntityCacheReader $cacheReader
     * @param FileLocator               $fileLocator
     */
    public function __construct(BusinessEntityCacheReader $cacheReader, FileLocator $fileLocator)
    {
        $this->cacheReader = $cacheReader;
        $this->fileLocator = $fileLocator;
    }

    /**
     * Warms up the cache.
     *
     * @return string
     */
    public function generate()
    {
        $businessEntities = $this->cacheReader->getBusinessClasses();
        $skeletonDirs = $this->fileLocator->locate('@VictoireCoreBundle/CacheWarmer/skeleton/');
        $this->setSkeletonDirs($skeletonDirs);

        return $this->render('EntityProxy.php.twig', array('businessEntities' => array_keys($businessEntities)));
    }
}
