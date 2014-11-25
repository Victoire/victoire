<?php
namespace Victoire\Bundle\I18nBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * This class build the entity EntityProxy with activated widgets relations
 **/
class I18nSubscriber implements EventSubscriber
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
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
        );
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public static function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();
        if ($metadata->getName() != 'Victoire\Bundle\I18nBundle\Entity\I18n') {
            return;
        } else {

        }
    }
}
