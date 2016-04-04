<?php

namespace Victoire\Bundle\WidgetBundle\Command;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Victoire\Bundle\WidgetBundle\Generator\WidgetGenerator;

/**
 * Create a new Widget for VictoireCMS.
 */
class MigrateWidgetOwningSideCommand extends ContainerAwareCommand
{
    protected $skeletonDirs;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:widget:migrate-owning-side')
            ->setDescription('widget is now the owning side of Widget<=>WidgetMap relation');
    }

    /**
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $widgetMaps = $entityManager->getRepository('VictoireWidgetMapBundle:WidgetMap')->findAll();

        $progress = $this->getHelper('progress');
        $progress->start($output, count($widgetMaps));
        foreach ($widgetMaps as $key => $widgetMap) {
            $widget = $widgetMap->getWidget();
            $widget->setWidgetMap($widgetMap);
            $widgetMap->setWidget(null);
            if ($key%100 == 0) {
                $entityManager->flush();
            }
            $progress->advance();
        }

        $entityManager->flush();

        $progress->finish();

    }

}
