<?php
namespace Victoire\Bundle\CoreBundle\Tests\Utils;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new \AppVentus\AjaxBundle\AvAjaxBundle(),
            new \Victoire\Bundle\CoreBundle\VictoireCoreBundle(),
            new \AppVentus\AsseticInjectorBundle\AvAsseticInjectorBundle(),
            new \AppVentus\PhpDocFillBundle\AvPhpDocFillBundle(),
            new \AppVentus\PixelArtBundle\AvPixelArtBundle(),
            new \Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new \JMS\TranslationBundle\JMSTranslationBundle(),
            new \JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new \JMS\AopBundle\JMSAopBundle(),

            new \Victoire\RedactorBundle\VictoireRedactorBundle(),
            new \Victoire\Bundle\BlogBundle\VictoireBlogBundle(),
            new \Victoire\TitleBundle\VictoireTitleBundle(),
            new \Victoire\LabelBundle\VictoireLabelBundle(),
            new \Victoire\TextBundle\VictoireTextBundle(),
            new \Victoire\ImageBundle\VictoireImageBundle(),
            new \Acme\Bundle\DemoBundle\AcmeDemoBundle(),
            new \Victoire\Widget\ListingBundle\VictoireWidgetListingBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new \Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/../../../../../app/config/config_'.$this->getEnvironment().'.yml');
    }
}
