<?php
namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Victoire\Bundle\CoreBundle\Theme\ThemeChain;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * This class listen Widget Entity metadata load and insert enabled widgets to it's DistriminatorMap.
 */
class WidgetDiscriminatorMapSubscriber implements EventSubscriber
{
    protected $widgets;
    protected $themeChain;


    /**
     * contructor
     * @param array $widgets
     */
    public function __construct($widgets, ThemeChain $themeChain)
    {
        $this->widgets = $widgets;
        $this->themeChain = $themeChain;
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
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadatas = $eventArgs->getClassMetadata();
        if ($metadatas->name == 'Victoire\Bundle\CoreBundle\Entity\Widget') {
            foreach ($this->widgets as $widget) {
                $metadatas->discriminatorMap[$widget['name']] = $widget['class'];
            }
        }
    }

}
