<?php
namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * This class listen Widget Entity metadata load and insert enabled widgets to it's DistriminatorMap.
 */
class WidgetDiscriminatorMapSubscriber implements EventSubscriber
{

    static protected $widgets;

    /**
     * contructor
     * @param array $widgets
     */
    public function setWidgets($widgets)
    {
        self::$widgets = $widgets;
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
     * Insert enabled widgets in base widget DiscriminatorMap
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    static public function loadClassMetadata($eventArgs)
    {
        //this functions is called during the extract of translations
        //but the argument is not the same
        //so to avoid an error during extractions, we test the argument
        if ($eventArgs instanceof LoadClassMetadataEventArgs) {
            $metadatas = $eventArgs->getClassMetadata();
            if ($metadatas->name === 'Victoire\Bundle\CoreBundle\Entity\Widget') {
                foreach (self::$widgets as $widget) {
                    $metadatas->discriminatorMap[$widget['name']] = $widget['class'];
                }
            }
        }
    }

}
