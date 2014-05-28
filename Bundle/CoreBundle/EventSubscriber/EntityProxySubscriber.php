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

    protected $annotationReader;


    /**
     * contructor
     * @param array $annotationReader
     */
    public function __construct($annotationReader)
    {
        $this->annotationReader = $annotationReader;
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
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadatas = $eventArgs->getClassMetadata();
        $metaBuilder = new ClassMetadataBuilder($metadatas);
        if ($metadatas->name == 'Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy') {
            foreach ($this->annotationReader->getBusinessClasses() as $field => $entity) {
                $metaBuilder->addManyToOne($field, $entity);
            }
        }
    }
}


