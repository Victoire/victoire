<?php

namespace Victoire\Bundle\CoreBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\CoreBundle\DependencyInjection\Compiler\AccessMapCompilerPass;
use Victoire\Bundle\CoreBundle\DependencyInjection\Compiler\TraductionCompilerPass;

/**
 * Victoire Core Bundle.
 */
class VictoireCoreBundle extends Bundle
{
    /**
     * Build bundle.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if ($container->hasDefinition('jms_translation.config_factory')) {
            $container->addCompilerPass(new TraductionCompilerPass());
        }
        $container->addCompilerPass(new AccessMapCompilerPass());
    }
}
