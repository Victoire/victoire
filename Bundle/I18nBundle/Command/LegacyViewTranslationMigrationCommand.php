<?php

namespace Victoire\Bundle\I18nBundle\Command;

use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\CoreBundle\Entity\Link;
use Victoire\Bundle\I18nBundle\Entity\ViewTranslation;
use Victoire\Bundle\I18nBundle\Entity\ViewTranslationLegacy;

class LegacyViewTranslationMigrationCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:legacy:view_translation')
            ->setDescription('migrate gedmo view translations into knp translations');
    }

    /**
     * Transform Gedmo translations into Knp translations
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progress = $this->getHelperSet()->get('progress');
        $progress->setProgressCharacter('V');
        $progress->setEmptyBarCharacter('-');

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $repo = $entityManager->getRepository('Victoire\Bundle\I18nBundle\Entity\ViewTranslationLegacy');
        $legacyTranslations = $repo->findAll();
        $progress->start($output, count($legacyTranslations));
        /** @var ViewTranslationLegacy $legacyTranslation */
        foreach ($legacyTranslations as $legacyTranslation) {
            $view = $legacyTranslation->getObject();
            $trans = $view->translate($legacyTranslation->getLocale(), false)->{'set'.$legacyTranslation->getField()}($legacyTranslation->getContent());
            $entityManager->persist($view);
            $view->mergeNewTranslations();

            $progress->advance();
        }
        
        $entityManager->flush();
        $progress->finish();

        $output->writeln(sprintf('<comment>Ok, %s view translations migrated !</comment>', count($legacyTranslations)));
    }
}
