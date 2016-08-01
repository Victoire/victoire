<?php

namespace Victoire\Bundle\WidgetBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

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
        $actions = [
            WidgetMap::ACTION_DELETE,
            WidgetMap::ACTION_OVERWRITE,
            WidgetMap::ACTION_CREATE,
        ];
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        $progress = $this->getHelper('progress');
        $progress->start($output, count($entityManager->getRepository('VictoireWidgetMapBundle:WidgetMap')->findAll()));

        foreach ($actions as $action) {
            $index = 0;
            $widgetMaps = $this->getWidgetMaps($index, $entityManager, $action);
            while (count($widgetMaps) > 0) {
                foreach ($widgetMaps as $key => $widgetMap) {
                    ++$index;
                    $widget = $widgetMap->getWidget();
                    $widget->setWidgetMap($widgetMap);
                    $progress->advance();
                }
                $entityManager->flush();
                $entityManager->clear();
                $widgetMaps = $this->getWidgetMaps($index, $entityManager, $action);
            }
        }

        $progress->finish();
    }

    private function getWidgetMaps($firstResult, EntityManager $entityManager, $action)
    {
        /** @var EntityRepository $repo */
        $repo = $entityManager->getRepository('VictoireWidgetMapBundle:WidgetMap');
        $qb = $repo->createQueryBuilder('widgetMap');
        $qb
            ->select('widgetMap')
            ->where('widgetMap.action = :widgetAction')
            ->setParameter(':widgetAction', $action)
            ->setFirstResult($firstResult)
            ->setMaxResults(50);

        $pag = new Paginator($qb);

        return $pag->getQuery()->getResult();
    }
}
