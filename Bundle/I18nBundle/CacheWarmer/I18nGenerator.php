<?php
namespace Victoire\Bundle\I18Bundle\CacheWarmer;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 *
 * @author Florian Raux
 *
 */
class I18nGenerator extends Generator
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
        $i18n= $this->annotationReader->getI18nlasses();

        $this->setSkeletonDirs(__DIR__."/skeleton/");

        return $this->render('I18n.php.twig', array('locales' => array_keys($locales)));
    }
}
