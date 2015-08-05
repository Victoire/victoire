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
    protected static $annotationReader;

    /**
     * contructor
     * @param array $annotationReader
     */
    public function setAnnotationReader($annotationReader)
    {
        self::$annotationReader = $annotationReader;
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
        //this functions is called during the extract of translations
        //but the argument is not the same
        //so to avoid an error during extractions, we test the argument
        if ($eventArgs instanceof LoadClassMetadataEventArgs) {
            $annotationReader = self::$annotationReader;

            $metadatas = $eventArgs->getClassMetadata();
            if ($metadatas->name === 'Victoire\Bundle\CoreBundle\Entity\EntityProxy') {
                foreach ($annotationReader->getBusinessClasses() as $field => $entity) {
                    if (!$metadatas->hasAssociation($field)) {
                        $metadatas->mapManyToOne(array(
                            'fieldName'    => $field,
                            'targetEntity' => $entity,
                            'cascade'      => array('persist'),
                            'inversedBy'   => 'proxies'
                            )
                        );
                        $metadatas->associationMappings[$field]['joinColumns'][0]['onDelete'] = "CASCADE";
                    }
                }
            }
            // Test if the current entity is a businessEntity
            $key = array_search($metadatas->name, $annotationReader->getBusinessClasses());
            // If so, and if proxies relation has already been injected (by a parent BusinessEntity)
            if ($key && !$metadatas->hasAssociation('proxies')) {
                $metaBuilder = new ClassMetadataBuilder($metadatas);
                $metaBuilder->addOneToMany('proxies', 'Victoire\Bundle\CoreBundle\Entity\EntityProxy', $key);
            }
        }
    }
}
