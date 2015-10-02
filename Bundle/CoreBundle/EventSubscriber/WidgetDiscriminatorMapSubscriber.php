<?php

namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Behat\Behat\Exception\Exception;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

/**
 * This class listen Widget Entity metadata load and insert enabled widgets to it's DistriminatorMap.
 */
class WidgetDiscriminatorMapSubscriber implements EventSubscriber
{
    protected static $widgets;

    /**
     * contructor.
     *
     * @param array $widgets
     */
    public function setWidgets($widgets)
    {
        self::$widgets = $widgets;
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
     * Insert enabled widgets in base widget DiscriminatorMap.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     *
     * @throws Exception
     */
    public static function loadClassMetadata($eventArgs)
    {
        //this functions is called during the extract of translations
        //but the argument is not the same
        //so to avoid an error during extractions, we test the argument
        if ($eventArgs instanceof LoadClassMetadataEventArgs) {
            $metadatas = $eventArgs->getClassMetadata();
            if ($metadatas->name === 'Victoire\Bundle\WidgetBundle\Entity\Widget') {
                foreach (self::$widgets as $widget) {
                    $class = $widget['class'];
                    if (!class_exists($class)) {
                        throw new \Exception('The class '.$class.' does not exists, please check the config.yml of the widget bundle.');
                    }
                    $metadatas->discriminatorMap[$widget['name']] = $class;
                }
            }
        }
    }
}
