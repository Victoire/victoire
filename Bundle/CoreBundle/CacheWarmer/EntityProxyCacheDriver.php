<?php
namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

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
        if (file_exists($entityProxy) && !class_exists("Victoire\Bundle\CoreBundle\Entity\EntityProxy")) {
            include_once $entityProxy;
        }
    }

    /**
     * Get all class names
     *
     * @return string[]
     */
    public function getAllClassNames()
    {
        return array('Victoire\Bundle\CoreBundle\Entity\EntityProxy');
    }
}
