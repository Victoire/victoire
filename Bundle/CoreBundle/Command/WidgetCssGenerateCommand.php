<?php

namespace Victoire\Bundle\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\TemplateBundle\Entity\Template;

class WidgetCssGenerateCommand extends ContainerAwareCommand
{
    /** @var EntityManager $entityManager */
    private $entityManager;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:widget-css:generate')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Generate CSS even for View\'s CSS up to date')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Number of generated View\'s CSS')
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
        $force = $input->getOption('force');
        $limit = $input->getOption('limit');

        //Get services
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $viewCssBuilder = $this->getContainer()->get('victoire_core.view_css_builder');
        $widgetMapBuilder = $this->getContainer()->get('victoire_widget_map.builder');
        $widgetRepo = $this->entityManager->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget');

        $views = $this->getViewsToTreat();

        //Remove View if CSS is upToDate and we don't want to regenerate it
        foreach ($views as $i => $view) {
            if (!$force && $view->isCssUpToDate()) {
                unset($views[$i]);
            }
        }

        //Prepare limit
        $limit = ($limit && $limit < count($views)) ? $limit : count($views);
        $count = 0;

        if (count($views) < 1) {
            $output->writeln('<info>0 View\'s CSS to regenerate for your options</info>');

            return true;
        }

        //Set progress for output
        $progress = new ProgressBar($output, $limit);
        $progress->start();

        foreach ($views as $view) {
            if ($count >= $limit) {
                break;
            }

            //If hash already exist, remove CSS file
            if ($viewHash = $view->getCssHash()) {
                $viewCssBuilder->removeCssFile($viewHash);
            }

            //Generate a new hash to force browser reload
            $view->changeCssHash();

            //Generate CSS file with its widgets style
            $widgetMapBuilder->build($view, $this->entityManager);
            $widgets = $widgetRepo->findAllWidgetsForView($view);
            $viewCssBuilder->generateViewCss($view, $widgets);

            //Set View's CSS as up to date
            $view->setCssUpToDate(true);
            $this->entityManager->persist($view);
            $this->entityManager->flush($view);

            $progress->advance();
            ++$count;
        }

        $progress->finish();

        return true;
    }

    /**
     * Get Templates, BasePages and ErrorPages.
     *
     * @return View[]
     */
    private function getViewsToTreat()
    {
        $templateRepo = $this->entityManager->getRepository('VictoireTemplateBundle:Template');
        $rootTemplates = $templateRepo->getInstance()
            ->where('template.template IS NULL')
            ->getQuery()
            ->getResult();
        $templates = [];
        $recursiveGetTemplates = function ($template) use (&$recursiveGetTemplates, &$templates) {
            array_push($templates, $template);
            foreach ($template->getInheritors() as $_template) {
                if ($_template instanceof Template) {
                    $recursiveGetTemplates($_template);
                }
            }
        };

        foreach ($rootTemplates as $rootTemplate) {
            $recursiveGetTemplates($rootTemplate);
        }

        $pageRepo = $this->entityManager->getRepository('VictoirePageBundle:BasePage');
        $pages = $pageRepo->findAll();
        $errorRepo = $this->entityManager->getRepository('VictoireTwigBundle:ErrorPage');
        $errorPages = $errorRepo->findAll();

        return array_merge($templates, array_merge($pages, $errorPages));
    }
}
