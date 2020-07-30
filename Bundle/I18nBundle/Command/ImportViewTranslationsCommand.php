<?php

namespace Victoire\Bundle\I18nBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportViewTranslationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:i18n:import_view_translations')
            ->setDescription('import view translations (csv)')
            ->addOption(
                'src',
                null,
                InputOption::VALUE_REQUIRED,
                'myFile.tsv, file.csv...'
            )
            ->addOption(
                'property',
                null,
                InputOption::VALUE_REQUIRED,
                'name, description...'
            )
            ->addOption(
                'delimiter',
                null,
                InputOption::VALUE_OPTIONAL,
                '\n...',
                '	'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $property = $input->getOption('property');
        $delimiter = $input->getOption('delimiter');

        /** @var ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $progress->setProgressCharacter('V');
        $progress->setEmptyBarCharacter('-');

        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $entityManager->getRepository('VictoireCoreBundle:View');

        $contentsOfFile = file_get_contents($input->getOption('src'));

        $lines = explode("\n", $contentsOfFile);
        $header = array_shift($lines);
        $header = explode($delimiter, $header);
        array_shift($header);

        $total = 0;
        $progress->start($output, count($lines));

        foreach ($lines as $line) {
            $translations = explode($delimiter, $line);
            $id = array_shift($translations);

            if ($view = $repo->find($id)) {
                foreach ($translations as $key => $translation) {
                    $locale = rtrim(strtolower($header[$key]), "\r");
                    $view->translate($locale)->{'set'.ucfirst($property)}($translation);
                }
                $view->mergeNewTranslations();
                $entityManager->flush();
            } else {
            }
            $progress->advance();
            $total++;
        }

        $progress->finish();

        $output->writeln(sprintf('<comment>Ok, %s translations updated !</comment>', $total));
    }
}
