<?php

namespace Victoire\Bundle\MediaBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Victoire\Bundle\MediaBundle\DependencyInjection\Compiler\MediaHandlerCompilerPass;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\MediaBundle\DependencyInjection\Compiler\MenuCompilerPass;

/**
 * VictoireMediaBundle
 */
class VictoireMediaBundle extends Bundle
{

    /**
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MediaHandlerCompilerPass());
        $container->addCompilerPass(new MenuCompilerPass());
    }
}
