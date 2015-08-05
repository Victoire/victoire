<?php
namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Victoire\Bundle\BusinessEntityBundle\Reader\BusinessEntityCacheReader;

/**
 * This class build the entity EntityProxy with activated widgets relations
 **/
class EntityProxySubscriber implements EventSubscriber
{
    protected static $cacheReader;

    /**
     * contructor
     * @param BusinessEntityCacheReader $cacheReader
     */
    public function setBusinessEntityCacheReader(BusinessEntityCacheReader $cacheReader)
    {
        self::$cacheReader = $cacheReader;
    }

    /**
     * bind to LoadClassMetadata method
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
        );
    }

    /**
     * Insert enabled widgets in base widget add relationship between BusinessEntities and EntityProxy
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public static function loadClassMetadata($eventArgs)
    {
        if ($eventArgs instanceof LoadClassMetadataEventArgs) {
            $metadatas = $eventArgs->getClassMetadata();
            if ($metadatas->name === 'Victoire\Bundle\CoreBundle\Entity\EntityProxy') {
                foreach (self::$cacheReader->getBusinessClasses() as $entity) {
                    if (!$metadatas->hasAssociation($entity->getId())) {
                        $metadatas->mapManyToOne(array(
                            'fieldName'    => $entity->getId(),
                            'targetEntity' => $entity->getClass(),
                            'cascade'      => array('persist'),
                            'inversedBy'   => 'proxies',
                            )
                        );
                        $metadatas->associationMappings[$entity->getId()]['joinColumns'][0]['onDelete'] = "CASCADE";
                    }
                }
            }

            // Test if the current entity is a businessEntity
            $key = array_search($metadatas->name, self::$cacheReader->getBusinessClasses());
            // If so, and if proxies relation has already been injected (by a parent BusinessEntity)
            if ($key && !$metadatas->hasAssociation('proxies')) {
                $metaBuilder = new ClassMetadataBuilder($metadatas);
                $metaBuilder->addOneToMany('proxies', 'Victoire\Bundle\CoreBundle\Entity\EntityProxy', $key);
            }
        }
    }
}
