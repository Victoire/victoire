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
            $metaBuilder = new ClassMetadataBuilder($metadatas);
            if ($metadatas->name === 'Victoire\Bundle\I18nBundle\Entity\I18n') {
                die('tst');
                exit;
                foreach ($annotationReader->getLocales() as $field => $entity) {
                    $metaBuilder->addOneToOne($field, $entity, "View");
                }
            }
        }
    }
}
