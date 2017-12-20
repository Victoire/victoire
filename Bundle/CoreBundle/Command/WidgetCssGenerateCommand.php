<?php

namespace Victoire\Bundle\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\CoreBundle\Entity\View;

class WidgetCssGenerateCommand extends ContainerAwareCommand
{
    const SPOOL_FLUSH = 30;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:widget-css:generate')
            ->addOption('outofdate', null, InputOption::VALUE_NONE, 'Generate CSS only for View\'s CSS not up to date')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Number of generated View\'s CSS')
            ->setDescription('Generate widgets CSS for each View');
    }

    /**
     * Generate a css file containing all css parameters for all widgets used in each view.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Get options
        $outOfDate = $input->getOption('outofdate');
        $limit = $input->getOption('limit');

        //Get services

        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $viewCssBuilder = $this->getContainer()->get('victoire_core.view_css_builder');
        $widgetMapBuilder = $this->getContainer()->get('victoire_widget_map.builder');

        $widgetRepo = $entityManager->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget');

        $viewRepo = $entityManager->getRepository('VictoireCoreBundle:View');
        $viewsCount = $entityManager->createQuery('SELECT COUNT(v)  FROM Victoire\Bundle\CoreBundle\Entity\View v')->getSingleScalarResult();

        //Prepare limit
        $limit = ($limit && $limit < $viewsCount) ? $limit : $viewsCount;
        $count = 0;

        //Set progress for output
        $progress = $this->getHelper('progress');
        $progress->start($output, $limit);

        for ($ii = 0; $ii <= $limit; $ii = $ii + self::SPOOL_FLUSH) {
            /* @var $views View[] */
            $views = $viewRepo->findBy([], ['updatedAt' => 'ASC'], self::SPOOL_FLUSH, $ii);

            foreach ($views as $view) {
                if ($outOfDate && $view->isCssUpToDate()) {
                    continue;
                }

                //Exit if limit reached
                if ($count >= $limit) {
                    break;
                }

                //If hash already exist, remove CSS file, else generate a hash
                if ($viewHash = $view->getCssHash()) {
                    $viewCssBuilder->removeCssFile($viewHash);
                } else {
                    $view->changeCssHash();
                }

                //Generate CSS file with its widgets style
                $widgetMapBuilder->build($view, $entityManager);
                $widgets = $widgetRepo->findAllWidgetsForView($view);
                $viewCssBuilder->generateViewCss($view, $widgets);

                //Set View's CSS as up to date
                $view->setCssUpToDate(true);
                $entityManager->persist($view);

                $count++;

                $progress->advance();
            }
            // Clear Entity Manager
            $entityManager->flush();
            $entityManager->clear();
        }
        $progress->finish();

        return true;
    }
}
