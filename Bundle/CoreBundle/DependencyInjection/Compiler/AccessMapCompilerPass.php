<?php
namespace Victoire\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler is used to inject the security for routes /victoire-dcms without needs to define it in the application security.yml
 *
 * @author  Paul Andrieux
 *
 **/
class AccessMapCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $path = '^/victoire-dcms';
        $serialized = serialize(array($path));
        $id = 'security.request_matcher.'.md5($serialized).sha1($serialized);
        $container
            ->register($id, '%security.matcher.class%')
            ->setPublic(false)
            ->setArguments(array($path));

        $definition = $container->getDefinition('security.access_map')
            ->addMethodCall('add', array(new Reference($id), array('ROLE_VICTOIRE')));

    }

}
