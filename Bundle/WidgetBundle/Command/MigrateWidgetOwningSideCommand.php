<?php

namespace Victoire\Bundle\WidgetBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            if ($key % 100 == 0) {
                $entityManager->flush();
            }
            $progress->advance();
        }

        $entityManager->flush();

        $progress->finish();
    }
}
