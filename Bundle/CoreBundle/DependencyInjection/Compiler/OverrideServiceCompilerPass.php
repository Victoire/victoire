<?php

namespace Victoire\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('assetic.filter.cssrewrite')->setPublic(true);
        $container->getDefinition('assetic.filter.less')->setPublic(true);
        $container->getDefinition('assetic.filter.scss')->setPublic(true);
        $container->getDefinition('assetic.filter_manager')->setPublic(true);
        $container->getDefinition('assetic.asset_manager')->setPublic(true);
        $container->getDefinition('victoire_core.entity_proxy.cache_driver')->setPublic(true);
    }
}
