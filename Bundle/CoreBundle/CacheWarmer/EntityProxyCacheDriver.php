<?php
namespace Victoire\Bundle\CoreBundle\CacheWarmer;


use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Victoire\Bundle\CoreBundle\Annotations\BusinessEntity;
use Doctrine\Common\Annotations\AnnotationReader as BaseAnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\StaticPHPDriver;

use Doctrine\Common\Cache\Cache,
    Doctrine\Common\Cache\ArrayCache,
    Doctrine\Common\Annotations\CachedReader;

/**
 * undocumented class
 *
 **/
class EntityProxyCacheDriver extends AnnotationDriver
{
    /**
     * construct
     *
     * @param unknown $reader
     * @param unknown $rootDir
     * @param unknown $env
     */
    public function __construct($reader, $rootDir, $env)
    {
        $this->env = $env;
        $this->reader = $reader;
        $this->rootDir = $rootDir;
        $entityProxy = $this->rootDir . "/cache/" . $this->env . "/victoire/Entity/EntityProxy.php";
        if (file_exists($entityProxy)) {
            include_once $entityProxy;
        }
    }

    /**
     * Get all class names
     *
     * @return array
     */
    public function getAllClassNames()
    {
        return array('Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy');
    }
}
