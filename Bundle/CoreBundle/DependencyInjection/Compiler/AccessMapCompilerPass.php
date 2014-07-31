<?php
namespace Victoire\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Description
 * ===========
 * This compiler is used to inject all victoire bundles to the jms traduction user interface
 * It simulates a custom config for all the overwritten Victoire bundle (src/Victoire/{YourBundleName}Bundle)
 * and sets the translation dir in its Resources/translations folder.
 *
 * @author  Paul Andrieux
 * @author  Leny Bernard
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
