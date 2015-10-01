<?php

namespace Victoire\Bundle\I18nBundle\CacheWarmer;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Config\FileLocator;

/**
 */
class I18nGenerator extends Generator
{
    protected $availableLocales;
    private $fileLocator;

    /**
     * Constructor.
     *
     * @param array       $availableLocales Got from I18n config
     * @param FileLocator $fileLocator
     */
    public function __construct($availableLocales, FileLocator $fileLocator)
    {
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

        return $this->render('I18n.php.twig', ['locales' => $this->applicationLocales]);
    }
}
