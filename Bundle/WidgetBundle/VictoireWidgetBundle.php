<?php

namespace Victoire\Bundle\WidgetBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\WidgetBundle\DependencyInjection\Compiler\WidgetContentResolverPass;

class VictoireWidgetBundle extends Bundle
{

    /**
     * Build bundle
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new WidgetContentResolverPass());
    }
}
