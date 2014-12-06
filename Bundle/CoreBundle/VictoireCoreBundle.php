<?php

namespace victoire\Bundle\CoreBundle;

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
     * create admin menu, add listeners for generate contextual menu item and dispatch globals item menus
     */
    public function boot()
    {
        $driverChain = $this->container->get('doctrine.orm.entity_manager')->getConfiguration()->getMetadataDriverImpl();

        $proxyDriver = $this->container->get('victoire_core.entity_proxy.cache_driver');
        $driverChain->addDriver($proxyDriver, 'Victoire');

    }

    /**
     * Build bundle
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
