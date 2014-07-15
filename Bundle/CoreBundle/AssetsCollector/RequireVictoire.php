<?php
namespace Victoire\Bundle\CoreBundle\AssetsCollector;
/**
 * This class compile provided assetic_injector.json file and inject defined resources in assetic
 */
class RequireVictoire
{
     protected $resources = array();
     protected $container;
     protected $widgetTypes;

     /**
      *
      * @param unknown $container
      */
     public function __construct($container)
     {
        //TODO : faire un objet qui prend tout le container n'est pas conseillé. Il faut passer juste les serivces nécessaires
        //TODO : Un service n'a pas détat. Ca n'a pas de sens de faire une fonction compute qui ne renvoit rient et un getter ensuite.
        $this->container = $container;
        $this->widgetTypes = array();
     }

    /**
     * This function computes the assets from assetic_injector.json require_victoire's tag
     * @param array $assets This assets as array
     * @return void
     */
    public function compute($assets)
    {
        foreach ($assets as $widget => $typeArray) {
            foreach ($typeArray as $type => $modeArray) {
                foreach ($modeArray as $mode => $paths) {
                    foreach ($paths as $path) {
                        $this->resources[$type][] = $path;
                    }
                }
            }
        }
    }

    /**
     * This function returns assets definend in assetic_injector.json file for tag require_victoire
     * @return $resources The assets
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * This function inform the collector that we need to include assets for widget of given $widgetType and for given $mode
     * @param string $widgetType The widget type (widget_*)
     * @param string $mode       The widget mode (show|edit)
     * @return void
     */
    public function addWidgetMode($widgetType, $mode)
    {
        $this->widgetTypes[$widgetType][] = $mode;
    }
}
