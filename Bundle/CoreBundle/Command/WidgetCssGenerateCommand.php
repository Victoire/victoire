<?php

namespace Victoire\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
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
            ->setDescription('Generate widgets css for each view');
    }

    /**
     * Generate a css file containing all css parameters for all widgets used in each view.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progress = $this->getHelper('progress');

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $viewRepo = $entityManager->getRepository('Victoire\Bundle\CoreBundle\Entity\View');
        $widgetRepo = $entityManager->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget');

        $viewCssBuilder = $this->getContainer()->get('victoire_core.view_css_builder');
        $widgetMapBuilder = $this->getContainer()->get('victoire_widget_map.builder');

        $viewCssBuilder->clearViewCssFolder();
        $views = $viewRepo->findAll();
        $progress->start($output, count($views));

        /* @var $views View[] */
        foreach ($views as $view) {
            if (!$viewHash = $view->getCssHash()) {
                $view->changeCssHash();
                $entityManager->persist($view);
                $entityManager->flush($view);
            }

            $widgetMapBuilder->build($view, true);
            $widgets = $widgetRepo->findAllWidgetsForView($view);
            $viewCssBuilder->generateViewCss($view, $widgets);

            $progress->advance();
        }

        $progress->finish();
    }
}
