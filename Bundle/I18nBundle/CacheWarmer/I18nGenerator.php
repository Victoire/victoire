<?php
namespace Victoire\Bundle\I18nBundle\CacheWarmer;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 *
 * @author Florian Raux
 *
 */
class I18nGenerator extends Generator
{
    private $annotationReader;
    protected $applicationLocales; 

    /**
     *
     * @param unknown $annotationReader
     */
    public function __construct($annotationReader, $applicationLocales)
    {
        $this->annotationReader = $annotationReader;
        $this->applicationLocales = $applicationLocales;
    }

    /**
     * Warms up the cache.
     *
     * @return string
     */
    public function generate()
    {

        $this->setSkeletonDirs(__DIR__."/Skeleton/");

        return $this->render('I18n.php.twig', array('locales' => $this->applicationLocales));
    }
}
