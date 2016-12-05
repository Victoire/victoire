<?php

namespace Victoire\Bundle\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\TemplateBundle\Entity\Template;

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

//        //Get View's CSS to generate
        $templateRepo = $entityManager->getRepository('VictoireTemplateBundle:Template');
        $rootTemplates = $templateRepo->getInstance()
            ->where('template.template IS NULL')
            ->getQuery()
            ->getResult();
        $templates = [];
        $recursiveGetTemplates = function ($template) use (&$recursiveGetTemplates, &$templates) {
            array_push($templates, $template);
            foreach ($template->getInheritors() as $template) {
                if ($template instanceof Template) {
                    $recursiveGetTemplates($template);
                }
            }
        };

        foreach ($rootTemplates as $rootTemplate) {
            $recursiveGetTemplates($rootTemplate);
        }

        $pageRepo = $entityManager->getRepository('VictoirePageBundle:BasePage');
        $pages = $pageRepo->findAll();
        $errorRepo = $entityManager->getRepository('VictoireTwigBundle:ErrorPage');
        $errorPages = $errorRepo->findAll();

        /* @var $views \Victoire\Bundle\CoreBundle\Entity\View[] */
        $views = array_merge($templates, array_merge($pages, $errorPages));

        //Prepare limit
        $limit = ($limit && $limit < count($views)) ? $limit : count($views);
        $count = 0;

        //Set progress for output
        $progress = $this->getHelper('progress');
        $progress->start($output, $limit);

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
            $widgetMapBuilder->build($view);
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
