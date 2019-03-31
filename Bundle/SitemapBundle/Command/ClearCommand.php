<?php

namespace Victoire\Bundle\SitemapBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCommand extends GenerateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('victoire:sitemap:clear')
            ->addOption('locale', 'l', InputOption::VALUE_OPTIONAL, 'The locale you want to clear', 'all')
            ->setDescription('Clear sitemap in cache');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Flusing %s...</info>', $input->getOption('locale')));
        foreach ($this->getLocalesFromInput($input) as $locale) {
            $output->write(sprintf('<info>♻ %s </info>', $locale));
            $this->redis->del("sitemap.$locale");
            $output->writeln('<info>🎉</info>');
        }
    }
}
