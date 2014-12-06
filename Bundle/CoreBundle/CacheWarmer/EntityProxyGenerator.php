<?php
namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Victoire\Bundle\CoreBundle\Annotations\Reader\AnnotationReader;

/**
 *
 * @author Paul Andrieux
 *
 */
class EntityProxyGenerator extends Generator
{
    private $annotationReader;
    private $fileLocator;

    /**
     * Constructor
     * @param AnnotationReader $annotationReader
     * @param FileLocator      $fileLocator
     */
    public function __construct(AnnotationReader $annotationReader, FileLocator $fileLocator)
    {
        $this->annotationReader = $annotationReader;
        $this->fileLocator = $fileLocator;
    }

    /**
     * Warms up the cache.
     *
     * @return string
     */
    public function generate()
    {
        $businessEntities = $this->annotationReader->getBusinessClasses();

        $skeletonDirs = $this->fileLocator->locate('@VictoireCoreBundle/CacheWarmer/skeleton/');
        $this->setSkeletonDirs($skeletonDirs);

        return $this->render('EntityProxy.php.twig', array('businessEntities' => array_keys($businessEntities)));
    }
}
