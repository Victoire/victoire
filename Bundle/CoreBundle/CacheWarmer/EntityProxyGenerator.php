<?php
namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 *
 * @author Paul Andrieux
 *
 */
class EntityProxyGenerator extends Generator
{
    private $annotationReader;

    /**
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
     * @return string
     */
    public function generate()
    {
        $businessEntities = $this->annotationReader->getBusinessClasses();

        $this->setSkeletonDirs(__DIR__."/skeleton/");

        return $this->render('EntityProxy.php.twig', array('businessEntities' => array_keys($businessEntities)));
    }
}
