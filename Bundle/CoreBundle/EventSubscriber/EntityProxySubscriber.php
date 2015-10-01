<?php

namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Victoire\Bundle\BusinessEntityBundle\Reader\BusinessEntityCacheReader;

/**
 * This class build the entity EntityProxy with activated widgets relations.
 **/
class EntityProxySubscriber implements EventSubscriber
{
    protected static $cacheReader;

    /**
     * contructor.
     *
     * @param BusinessEntityCacheReader $cacheReader
     */
    public function setBusinessEntityCacheReader(BusinessEntityCacheReader $cacheReader)
    {
        self::$cacheReader = $cacheReader;
    }

    /**
     * bind to LoadClassMetadata method.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata',
        ];
    }

    /**
     * Insert enabled widgets in base widget add relationship between BusinessEntities and EntityProxy.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public static function loadClassMetadata($eventArgs)
    {
        if ($eventArgs instanceof LoadClassMetadataEventArgs) {
            /** @var ClassMetadata $metadatas */
            $metadatas = $eventArgs->getClassMetadata();
            if ($metadatas->name === 'Victoire\Bundle\CoreBundle\Entity\EntityProxy') {
                foreach (self::$cacheReader->getBusinessClasses() as $entity) {
                    if (!$metadatas->hasAssociation($entity->getId())) {
                        $metadatas->mapOneToOne([
                            'fieldName'    => $entity->getId(),
                            'targetEntity' => $entity->getClass(),
                            'cascade'      => ['persist'],
                            ]
                        );
                        $metadatas->associationMappings[$entity->getId()]['joinColumns'][0]['onDelete'] = 'CASCADE';
                    }
                }
            }
        }
    }
}
