<?php

namespace Victoire\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\CoreBundle\Entity\View;

class WidgetCssGenerateCommand extends ContainerAwareCommand
{
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
     * @return boolean
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Get options
        $outOfDate = $input->getOption('outofdate');
        $limit = $input->getOption('limit');

        //Get services
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $viewCssBuilder = $this->getContainer()->get('victoire_core.view_css_builder');
        $widgetMapBuilder = $this->getContainer()->get('victoire_widget_map.builder');

        //Get repositories
        $viewRepo = $entityManager->getRepository('Victoire\Bundle\CoreBundle\Entity\View');
        $widgetRepo = $entityManager->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget');

        //Get View's CSS to generate
        /* @var $views View[] */
        $views = $outOfDate ? $viewRepo->findByCssUpToDate(false) : $viewRepo->findAll();

        //Prepare limit
        $limit = ($limit && $limit < count($views)) ? $limit : count($views);
        $count = 0;

        //Set progress for output
        $progress = $this->getHelper('progress');
        $progress->start($output, $limit);

        foreach ($views as $view) {

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
            $widgetMapBuilder->build($view, true);
            $widgets = $widgetRepo->findAllWidgetsForView($view);
            $viewCssBuilder->generateViewCss($view, $widgets);

            //Set View's CSS as up to date
            $view->setCssUpToDate(true);
            $entityManager->persist($view);
            $entityManager->flush($view);

            ++$count;
            $progress->advance();
        }

        $progress->finish();

        return true;
    }
}
