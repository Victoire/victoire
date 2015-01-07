<?php

namespace Victoire\Bundle\I18nBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class I18nCompilerPass implements CompilerPassInterface
{
    /**
    * method to replace class by other classes during compilation
    *
    * @param ContainerBuilder $container
    */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('translator.default');
        $definition->setClass('Victoire\Bundle\I18nBundle\Translation\Translator');

        $definition = $container->getDefinition('victoire_widget.twig.link_extension');
        $definition->setClass('Victoire\Bundle\I18nBundle\Twig\I18nLinkExtension');
        $definition->setArguments(array(
            new Reference('router'),
            new Reference('request_stack'),
            '%victoire_seo.analytics%',
            new Reference('victoire_core.helper.business_entity_helper'),
            new Reference('victoire_business_entity_page.business_entity_page_helper'),
            new Reference('victoire_page.page_helper'),
            new Reference('victoire_i18n.locale_resolver')
            )
        );

    }
}
