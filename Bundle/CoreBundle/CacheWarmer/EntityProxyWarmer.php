<?php
namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Victoire\Bundle\BusinessEntityBundle\Annotation\AnnotationDriver;
use Victoire\Bundle\BusinessEntityBundle\Generator\EntityProxyGenerator;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;

/**
 * The EntityProxyWarmer object, called in warmup event parse all objects, save in apc
 * and instanciate the EntityProxyGenerator to generate the entity proxy class.
 */
class EntityProxyWarmer extends CacheWarmer
{
    private $annotationDriver;
    private $businessEntityHelper;
    private $fileLocator;

    /**
     * Constructor
     * @param AnnotationDriver     $annotationDriver
     * @param BusinessEntityHelper $businessEntityHelper
     * @param FileLocator          $fileLocator
     *
     */
    public function __construct(AnnotationDriver $annotationDriver, BusinessEntityHelper $businessEntityHelper, FileLocator $fileLocator)
    {
        $this->annotationDriver = $annotationDriver;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->fileLocator = $fileLocator;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     *
     * @throws Exception
     */
    public function warmUp($cacheDir)
    {
        //parse annotations and save in cache
        $classNames = $this->annotationDriver->getAllClassnames();
        $businessEntities = $this->annotationDriver->getBusinessEntities();

        var_dump($className);
        foreach ($businessEntities as $businessEntity) {
            $this->businessEntityHelper->saveBusinessEntity($businessEntity);
        }
        $this->generateEntityProxyFile($cacheDir);
    }

    /**
     * IS the warmer optionnal
     *
     * @return boolean
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * Generate the EntityProxy file in cache
     *
     * @return void
     */
    protected function generateEntityProxyFile($cacheDir)
    {
        $dir = $cacheDir.'/victoire/Entity';
        $file = $dir.'/EntityProxy.php';

        if (!file_exists($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create directory "%s".', $dir));
            }
        }

        $generator = new EntityProxyGenerator($this->businessEntityHelper, $this->fileLocator);
        $cacheContent = $generator->generate();

        $this->writeCacheFile($file, $cacheContent);
        if (!class_exists("Victoire\Bundle\CoreBundle\Entity\EntityProxy")) {
            include_once $file;
        }
    }
}
