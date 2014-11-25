<?php

namespace Victoire\Bundle\I18nBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class I18nCompilerPass implements CompilerPassInterface
{
	/**
	* method to replace class by other classes during compilation
	*
	* @param ContainerBuilder $container
	*/
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('victoire_core.page_menu_listener');
        $definition->setClass('Victoire\Bundle\I18nBundle\Listener\I18nMenuListener');
        
    }
}
