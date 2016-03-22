<?php

namespace Victoire\Bundle\I18nBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\I18nBundle\DependencyInjection\Compiler\I18nCompilerPass;

class VictoireI18nBundle extends Bundle
{

    /**
     * Build bundle.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new I18nCompilerPass());
    }
}
