<?php
namespace Victoire\Bundle\I18nBundle\CacheWarmer;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Config\FileLocator;
use Victoire\Bundle\CoreBundle\Annotations\Reader\AnnotationReader;

/**
 *
 * @author Florian Raux
 *
 */
class I18nGenerator extends Generator
{
    private $annotationReader;
    protected $availableLocales;
    private $fileLocator;

    /**
     * Constructor
     * @param AnnotationReader $annotationReader
     * @param array            $availableLocales Got from I18n config
     * @param FileLocator      $fileLocator
     */
    public function __construct(AnnotationReader $annotationReader, $availableLocales, FileLocator $fileLocator)
    {
        $this->annotationReader = $annotationReader;
	$this->fileLocator = $fileLocator;
        $this->applicationLocales = $availableLocales;
    }

    /**
     * Warms up the cache.
     *
     * @return string
     */
    public function generate()
    {
	$skeletonDirs = $this->fileLocator->locate('@VictoireI18nBundle/CacheWarmer/Skeleton/');
	$this->setSkeletonDirs($skeletonDirs);

        return $this->render('I18n.php.twig', array('locales' => $this->applicationLocales));
    }
}
