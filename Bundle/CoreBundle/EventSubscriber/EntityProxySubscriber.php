<?php
namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * This class build the entity EntityProxy with activated widgets relations
 **/
class EntityProxySubscriber implements EventSubscriber
{
    protected static $cacherReader;

    /**
     * contructor
     * @param array $cacherReader
     */
    public function setBusinessEntityCacheReader($cacherReader)
    {
        self::$cacherReader = $cacherReader;
    }

    /**
     * bind to LoadClassMetadata method
     *
     * @return array
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
        //this functions is called during the extract of translations
        //but the argument is not the same
        //so to avoid an error during extractions, we test the argument
        if ($eventArgs instanceof LoadClassMetadataEventArgs) {

            $metadatas = $eventArgs->getClassMetadata();
            $metaBuilder = new ClassMetadataBuilder($metadatas);
            if ($metadatas->name === 'Victoire\Bundle\CoreBundle\Entity\EntityProxy') {
                foreach (self::$cacherReader->getBusinessClasses() as $field => $entity) {
                    $metaBuilder->addManyToOne($field, $entity, "proxies");
                }
            }
            $key = array_search($metadatas->name, self::$cacherReader->getBusinessClasses());
            if ($key) {
                $metaBuilder->addOneToMany('proxies', 'Victoire\Bundle\CoreBundle\Entity\EntityProxy', $key);
            }
        }
    }
}
