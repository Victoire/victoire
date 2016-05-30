<?php

namespace Victoire\Bundle\I18nBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
            ->setDescription('migrate gedmo view translations into knp translations')
            ->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'could be "all" to migrate all, "views" to migrate only views or "articles" to migrate only articles', 'all');
    }

    /**
     * Transform Gedmo translations into Knp translations.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mode = $input->getOption('mode');
        $progress = $this->getHelperSet()->get('progress');
        $progress->setProgressCharacter('V');
        $progress->setEmptyBarCharacter('-');

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $legacyTranslations = $legacyArticles = [];
        if ('all' === $mode || 'views' === $mode) {
            $repo = $entityManager->getRepository('Victoire\Bundle\I18nBundle\Entity\ViewTranslationLegacy');
            $legacyTranslations = $repo->findAll();
        }
        if ('all' === $mode || 'articles' === $mode) {
            $repoArticle = $entityManager->getRepository('Victoire\Bundle\BlogBundle\Entity\Article');
            $legacyArticles = $repoArticle->findAll();
        }
        $total = count($legacyTranslations) + count($legacyArticles);
        $progress->start($output, $total);
        /** @var ViewTranslationLegacy $legacyTranslation */
        foreach ($legacyTranslations as $legacyTranslation) {
            $view = $legacyTranslation->getObject();
            $trans = $view->translate($legacyTranslation->getLocale(), false)->{'set'.$legacyTranslation->getField()}($legacyTranslation->getContent());
            $entityManager->persist($view);
            $view->mergeNewTranslations();

            $progress->advance();
        }

        foreach ($legacyArticles as $legacyArticle) {
            $legacyArticleTranslation = $legacyArticle->translate('fr', false);
            $legacyArticleTranslation->setName($legacyArticle->getName());
            $legacyArticleTranslation->setSlug($legacyArticle->getSlug());
            $legacyArticleTranslation->setDescription($legacyArticle->getDescription());
            $entityManager->persist($legacyArticleTranslation);
            $legacyArticle->mergeNewTranslations();

            $progress->advance();
        }

        $entityManager->flush();
        $progress->finish();

        $output->writeln(sprintf('<comment>Ok, %s translations migrated !</comment>', count($total)));
    }
}
