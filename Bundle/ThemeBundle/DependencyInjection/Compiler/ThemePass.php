<?php

namespace Victoire\Bundle\ThemeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 *
 * @author Paul Andrieux
 *
 */
class ThemePass implements CompilerPassInterface
{
    /**
     * Create the list of services that are tagged as a theme for a widget
     *
     * @param ContainerBuilder $container The container
     *
     * @throws \Exception The widget property is missing
     */
    public function process(ContainerBuilder $container)
    {
        $themeHelperExists = $container->hasDefinition('victoire_core.theme_chain');

        //if the theme helper exists
        if ($themeHelperExists) {
            //get the service
            $definition = $container->getDefinition('victoire_core.theme_chain');

            //get the list of services that have the tag victoire_core.theme
            $taggedServices = $container->findTaggedServiceIds('victoire_core.theme');

            //parse the services that have the tag victoire_core.theme
            foreach ($taggedServices as $themeReference => $attributes) {

                //test that the property widget is set
                if (!isset($attributes[0]['widget'])) {
                    throw new \Exception('The manager tagged as victoire_core.theme must have a widget property, please fix your service.yml.');
                }

                //name of the widget that have the theme
                $widgetName = $attributes[0]['widget'];

                //remove the victoire.widget prefix
                $widgetTheme = substr($themeReference, strlen('victoire.widget.'));

                //add the reference to the manager as a theme for the widget
                $definition->addMethodCall('addTheme', array($widgetTheme, $widgetName));
            }
        }
    }
}
