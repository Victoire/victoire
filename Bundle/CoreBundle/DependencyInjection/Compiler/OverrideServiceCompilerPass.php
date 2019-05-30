<?php

namespace Victoire\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if($container->hasDefinition('assetic.filter.cssrewrite')) {
            $container->getDefinition('assetic.filter.cssrewrite')->setPublic(true);
        }
        if($container->hasDefinition('assetic.filter.less')) {
            $container->getDefinition('assetic.filter.less')->setPublic(true);
        }
        if($container->hasDefinition('assetic.filter.scss')) {
            $container->getDefinition('assetic.filter.scss')->setPublic(true);
        }
        if($container->hasDefinition('assetic.filter_manager')) {
            $container->getDefinition('assetic.filter_manager')->setPublic(true);
        }
        if($container->hasDefinition('assetic.asset_manager')) {
            $container->getDefinition('assetic.asset_manager')->setPublic(true);
        }
        if($container->hasDefinition('victoire_core.entity_proxy.cache_driver')) {
            $container->getDefinition('victoire_core.entity_proxy.cache_driver')->setPublic(true);
        }
    }
}
