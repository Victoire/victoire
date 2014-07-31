<?php

namespace Victoire\Bundle\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Victoire\Bundle\CoreBundle\Listener\PageMenuListener;
use Victoire\Bundle\CoreBundle\DependencyInjection\Compiler\TraductionCompilerPass;
use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        $driverChain->addDriver($proxyDriver, 'Victoire\Bundle\CoreBundle\Cached\Entity');

    }

    /**
     * Build bundle
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TraductionCompilerPass());
    }
}
