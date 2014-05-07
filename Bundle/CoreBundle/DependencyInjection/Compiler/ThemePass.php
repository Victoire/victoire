<?php
namespace Victoire\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ThemePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        if ($container->hasDefinition('victoire_cms.theme_chain')) {

            $definition = $container->getDefinition(
                'victoire_cms.theme_chain'
            );

            $taggedServices = $container->findTaggedServiceIds(
                'victoire_cms.theme'
            );
            foreach ($taggedServices as $id => $attributes) {
                $definition->addMethodCall(
                    'addTheme',
                    array(new Reference($id), $attributes[0]['widget'])
                );
            }
        }

    }
}
