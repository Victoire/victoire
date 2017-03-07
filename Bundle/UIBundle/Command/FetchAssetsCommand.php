<?php

namespace Victoire\Bundle\UIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class FetchAssetsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:ui:fetchAssets')
            ->setDescription('Fetch every assets (with bower)');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<fg=white;bg=cyan;options=bold>Fetching Bower dependencies</>');
        $process = new Process('cd vendor/victoire/victoire/Bundle/UIBundle/Ressources && bower install');
        try {
            $process->mustRun();

            echo $process->getOutput();
        } catch (ProcessFailedException $e) {
            $output->writeln(sprintf('<error>%s</error>', 'Did you installed bower properly ?'));
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }

        $output->writeln(sprintf('<info>%s</info>', $process->getOutput()));
        $output->writeln('✅  <fg=green>Ok</> Assets fetched');
    }
}
