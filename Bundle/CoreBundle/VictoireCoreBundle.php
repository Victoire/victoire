<?php

namespace Victoire\Bundle\CoreBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\CoreBundle\DependencyInjection\Compiler\AccessMapCompilerPass;
use Victoire\Bundle\CoreBundle\DependencyInjection\Compiler\TraductionCompilerPass;

/**
 * Awesome Cms for Symfony2
 */
class VictoireCoreBundle extends Bundle
{

    /**
     * Build bundle
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TraductionCompilerPass());
        $container->addCompilerPass(new AccessMapCompilerPass());
    }
}
