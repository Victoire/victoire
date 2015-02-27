<?php
namespace Victoire\Bundle\I18nBundle\CacheWarmer;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

/**
 * undocumented class
 *
 **/
class I18nCacheDriver extends AnnotationDriver
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
        $i18n = $this->cacheDir . "/victoire/Entity/I18n.php";
        if (file_exists($i18n) && !class_exists("Victoire\Bundle\I18nBundle\Entity\I18n")) {
            include_once $i18n;
        }
    }

    /**
     * Get all class names
     *
     * @return string[]
     */
    public function getAllClassNames()
    {
        return array('Victoire\Bundle\I18nBundle\Entity\I18n');
    }
}
