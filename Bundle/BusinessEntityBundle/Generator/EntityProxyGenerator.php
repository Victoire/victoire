<?php
namespace Victoire\Bundle\BusinessEntityBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;

/**
 * fetch BusinessEntity cache and generate the EntityProxy class to be mapped by doctrine.
 */
class EntityProxyGenerator extends Generator
{
    private $businessEntityHelper;
    private $fileLocator;

    /**
     * Constructor
     * @param BusinessEntityHelper $businessEntityHelper
     * @param FileLocator          $fileLocator
     */
    public function __construct(BusinessEntityHelper $businessEntityHelper, FileLocator $fileLocator)
    {
        $this->businessEntityHelper = $businessEntityHelper;
        $this->fileLocator = $fileLocator;
    }

    /**
     * Warms up the cache.
     *
     * @return string
     */
    public function generate()
    {
        $businessEntities = $this->businessEntityHelper->getBusinessEntities();
        $skeletonDirs = $this->fileLocator->locate('@VictoireCoreBundle/CacheWarmer/skeleton/');
        $this->setSkeletonDirs($skeletonDirs);

        return $this->render('EntityProxy.php.twig', array('businessEntities' => array_keys($businessEntities)));
    }
}
