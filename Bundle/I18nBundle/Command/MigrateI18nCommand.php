<?php
namespace Victoire\Bundle\I18nBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\I18nBundle\Entity\I18n;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Update a site to be compatible with i18n
 */
class MigrateI18nCommand extends ContainerAwareCommand
{
    /**
    * this method is the configuration of the command
    */
    protected function configure()
    {
        $this
            ->setName('victoire:migrate:i18n')
            ->setDescription('Migrate a database to be I18nalizable')
            ->addArgument('default-locale', InputArgument::OPTIONAL, 'What is your default locale ?')
        ;
    }

    /**
    * @param InputInterface $input
    * @param OutpuInterface $output
    *
    * this method is executed when we launch the command
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $defaultLocale = $input->getArgument('default-locale');
        $defaultLocale = $defaultLocale ? $defaultLocale : 'fr';
        $this->doUpdate($output, $defaultLocale);
    }

    /**
    * @param OutputInterface $output
    * @param $locale the current locale of the application
    *
    * this method allow you to update the schema with the new i18n entity and its relations.
    *  it also sets all your views to the locale given in parameter and build the links
    */
    protected function doUpdate(OutputInterface $output, $locale)
    {
        $container = $this->getApplication()->getKernel()->getContainer();

        $entityManager = $container->get('doctrine.orm.entity_manager');

        $command = $this->getApplication()->find('doctrine:schema:update');
        $doctrineInput = new ArrayInput(
            array(
                'command' => 'doctrine:schema:update',
                '--force' => true
            )
        );
        $command->run($doctrineInput, $output);
        $views = $entityManager->getRepository('VictoireCoreBundle:View')->findAll();

        foreach ($views as $view) {
            $view->setLocale($locale);
            $i18n = new I18n();
            $entityManager->persist($i18n);
            $i18n->setTranslation($locale, $view);
            $view->setI18n($i18n);
            $entityManager->persist($view);
        }

        $entityManager->flush();
        $this->doGenerateViewCache($output);
    }
    /**
    * @param OutputInterface $output
    *
    * this call the command victoire:generate:view-cache
    */
    protected function doGenerateViewCache(OutputInterface $output)
    {
        $command = $this->getApplication()->find('victoire:generate:view-cache');
        $doctrineInput = new ArrayInput(
            array(
                'command' => 'victoire:generate:view-cache'
            )
        );
        $command->run($doctrineInput, $output);
    }
}
