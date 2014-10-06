<?php
namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

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
     * @param string  $cacheDir
     */
    public function __construct($reader, $cacheDir)
    {
        $this->reader = $reader;
        $this->cacheDir = $cacheDir;
        $entityProxy = $this->cacheDir . "/victoire/Entity/EntityProxy.php";
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
        return array('Victoire\Bundle\CoreBundle\Entity\EntityProxy');
    }
}
