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
            // $themes = $this->themeChain->getThemes();
            // foreach ($themes as $widgetTheme) {
            //     foreach ($widgetTheme as $theme) {
            //         $metadatas->discriminatorMap[$theme->getName()] = $theme->getClass();
            //     }
            // }

        }
        // $themes = $this->themeChain->getThemes();

        // if (array_key_exists($metadatas->name, $themes)) {


        //     // $metadatas->inheritanceType = ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_TABLE;
        //     $discriminatorColumn = array(
        //         'name' => 'theme',
        //         'type' => 'string',
        //         'length' => null,
        //         'columnDefinition' => null,
        //         'fieldName' => 'theme'
        //     );
        //     $metadatas->discriminatorColumn = $discriminatorColumn;

        //     foreach ($themes[$metadatas->name] as $theme) {
        //         $metadatas->discriminatorMap[$theme->getName()] = $theme->getClass();
        //     }
        // }
    }

}
