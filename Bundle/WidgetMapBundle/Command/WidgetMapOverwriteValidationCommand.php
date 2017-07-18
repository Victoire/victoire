<?php

namespace Victoire\Bundle\WidgetMapBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

class WidgetMapOverwriteValidationCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:widget-map:validate-overwrite')
            ->setDescription('Check for each View if there is conflicts between WidgetMaps.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders([
            'Contextual View',
            'WidgetMaps in conflict',
            'Parent WidgetMap',
            'Slot',
            'Position',
        ]);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $contextualViewWarmer = $this->getContainer()->get('victoire_widget_map.contextual_view_warmer');

        $conflictNb = 0;

        $views = $entityManager->getRepository('Victoire\Bundle\CoreBundle\Entity\View')->findAll();
        foreach ($views as $view) {
            $widgetMaps = $contextualViewWarmer->warm($view);

            foreach ($widgetMaps as $widgetMap) {
                $positions = [WidgetMap::POSITION_BEFORE, WidgetMap::POSITION_AFTER];
                $children = [];
                foreach ($positions as $position) {
                    $children[$position] = null;
                    $matchingChildren = [];

                    foreach ($widgetMap->getContextualChildren($position) as $_child) {
                        if (null === $_child->getSubstituteForView($view)) {
                            $children[$position] = $_child;
                            $matchingChildren[] = $_child->getId();
                            $parent = $_child->getParent()->getId();
                            $slot = $_child->getSlot();
                        }
                    }

                    if (!$children[$position] && $widgetMap->getReplaced()) {
                        foreach ($widgetMap->getReplaced()->getContextualChildren($position) as $_child) {
                            if (null === $_child->getSubstituteForView($view)) {
                                $matchingChildren[] = $_child->getId();
                                $parent = $_child->getParent()->getId();
                                $slot = $_child->getSlot();
                            }
                        }
                    }

                    if (count($matchingChildren) > 1) {
                        $conflictNb++;
                        $table->addRow([
                            $view->getId(),
                            implode(', ', $matchingChildren),
                            $parent,
                            $slot,
                            $position,
                        ]);
                    }
                }
            }
        }

        if ($conflictNb > 0) {
            $output->writeln(sprintf('<error>%s WidgetMaps conflicts found.</error>', $conflictNb));
            $output->writeln('<error>At least one of those WidgetMaps must have a replace_id with action "overwrite".</error>');
            $table->render();
        } else {
            $output->writeln('<info>No conflict found.</info>');
        }
    }
}
