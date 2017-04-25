<?php

namespace Victoire\Bundle\WidgetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clear widget redis cache.
 */
class ClearWidgetCacheCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:widget:cache:clear')
            ->setDescription('Clear the widget redis cache');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $widgetCache = $this->getContainer()->get('victoire_widget.widget_cache');

        $widgetCache->clear();

        $output->writeln('Widget cache has been cleared');
    }
}
