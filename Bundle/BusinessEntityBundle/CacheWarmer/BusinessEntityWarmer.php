<?php

namespace Victoire\Bundle\BusinessEntityBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Victoire\Bundle\BusinessEntityBundle\Annotation\AnnotationDriver;
use Victoire\Bundle\BusinessEntityBundle\Generator\EntityProxyGenerator;

/**
 * The BusinessEntityWarmer object, called in warmup event parse all objects, save in apc
 * and instanciate the EntityProxyGenerator to generate the entity proxy class.
 * ref: victoire_business_entity.warmer.
 */
class BusinessEntityWarmer extends CacheWarmer
{
    private $driver;

    /**
     * Constructor.
     *
     * @param AnnotationDriver $driver
     */
    public function __construct(AnnotationDriver $driver)
    {
        $this->driver = $driver;
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
        foreach ($this->driver->getAllClassNames() as $className) {
            $this->driver->parse(new \ReflectionClass($className));
        }
    }

    /**
     * IS the warmer optionnal.
     *
     * @return bool
     */
    public function isOptional()
    {
        return false;
    }
}
