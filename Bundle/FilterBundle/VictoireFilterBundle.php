<?php

namespace Victoire\Bundle\FilterBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Victoire\Bundle\FilterBundle\DependencyInjection\Compiler\FilterCompilerPass;

/**
 *
 */
class VictoireFilterBundle extends Bundle
{
    /**
     * Build bundle
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FilterCompilerPass());
    }
}
