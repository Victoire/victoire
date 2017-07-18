<?php

namespace Victoire\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;

/**
 * Description
 * ===========
 * This compiler is used to inject all victoire bundles to the jms traduction user interface
 * It simulates a custom config for all the overwritten Victoire bundle (src/Victoire/{YourBundleName}Bundle)
 * and sets the translation dir in its Resources/translations folder.
 *
 * @author Paul Andrieux
 * @author Leny Bernard
 **/
class TraductionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('jms_translation.config_factory')) {
            $definition = $container->getDefinition('jms_translation.config_factory');
            $victoireBasePath = $container->getParameterBag()->get('kernel.root_dir').'/../src/Victoire';
            if (file_exists($victoireBasePath)) {
                $finder = new Finder();
                $finder->directories()->in($victoireBasePath)->depth(' == 0');

                // we iterates on each overwritten or home made Victoire bundle (in src path)
                foreach ($finder as $bundle) {
                    //We inject a new config in jms_translation
                    $def = new Definition('JMS\TranslationBundle\Translation\ConfigBuilder');
                    $def->addMethodCall('setTranslationsDir', [$victoireBasePath.'/'.$bundle->getFilename().'/Resources/translations']);
                    $def->addMethodCall('setScanDirs', [[$victoireBasePath.'/'.$bundle->getFilename()]]);

                    $definition->addMethodCall(
                        'addBuilder',
                        [$bundle->getFilename(), $def]
                    );
                }
            }
        }
    }
}
