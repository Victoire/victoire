<?php
namespace Victoire\Bundle\I18nBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\I18nBundle\Entity\I18n;

/**
 * Update a site to be compatible with i18n
 */
class UpdateToI18nCommand extends ContainerAwareCommand
{
	protected function configure()
    {
        $this
            ->setName('victoire:update:i18n')
            ->setDescription('updater la base de donnée en I18n')
            ->addArgument('default-locale', InputArgument::OPTIONAL, 'Quelle est la langue de votre site?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $defaultLocale = $input->getArgument('default-locale');
        $defaultLocale = $defaultLocale ? $defaultLocale : 'fr';
        $this->doUpdate($defaultLocale);
    }

    protected function doUpdate($locale) 
    {
    	$container = $this->getApplication()->getKernel()->getContainer();
    	$em = $container->get('@doctrine.orm.entity_manager');
    	$views = $em->getRepository('VictoireCoreBundle:View')->findAll();

    	foreach ($views as $view) {
    		$view->setLocale($locale);
    		$view->setI18n(new I18n());
    		$view->setTranslation($locale, $locale);
    		$em->persist($view);
    	}

    	$em->flush();
    }
}
