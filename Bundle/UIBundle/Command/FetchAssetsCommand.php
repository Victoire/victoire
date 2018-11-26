<?php

namespace Victoire\Bundle\UIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class FetchAssetsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:ui:fetchAssets')
            ->addArgument('victoireUIConfigPath',
                InputArgument::OPTIONAL,
                'The victoire vendor path',
                'vendor/victoire/victoire/Bundle/UIBundle/Resources/config'
            )
            ->addArgument('bowerPath',
                InputArgument::OPTIONAL,
                'The bower path',
                'bower'
            )
            ->addArgument('yarnPath',
                InputArgument::OPTIONAL,
                'The yarn path',
                'yarn'
            )
            ->addOption('force',
                null,
                InputOption::VALUE_NONE,
            'If dependencies are installed, it reinstalls all installed components. It also forces installation even when there are non-bower directories with the same name in the components directory. Also bypasses the cache and overwrites to the cache anyway.'
            )
            ->setDescription('Fetch every assets (with bower and npm)');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileSystem = new Filesystem();
        $victoireUIConfigPath = rtrim($input->getArgument('victoireUIConfigPath'), '/');
        if (!$fileSystem->exists(sprintf(
            '%s/%s',
            $this->getContainer()->getParameter('kernel.project_dir'),
            $victoireUIConfigPath
        ))) {
            throw new \Exception(sprintf('The `%s` directory does not exist.', $victoireUIConfigPath));
        }
        $bowerPath = $input->getArgument('bowerPath');
        $force = $input->getOption('force');
        $output->writeln('<fg=white;bg=cyan;options=bold>Fetching Bower dependencies</>');
        $bowerProcess = new Process(sprintf(
            'cd %s && %s install %s',
            $victoireUIConfigPath,
            $bowerPath,
            $force ? '--force' : ''
        ));

        try {
            $bowerProcess->mustRun();

            echo $bowerProcess->getOutput();
        } catch (ProcessFailedException $e) {
            $output->writeln(sprintf('<error>%s</error>', 'Did you installed bower properly ?'));
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }

        $output->writeln(sprintf('<info>%s</info>', $bowerProcess->getOutput()));
        $output->writeln('<fg=green>Ok</> Bower assets fetched');

        //NPM
        $yarnPath = $input->getArgument('yarnPath');
        $output->writeln('<fg=white;bg=cyan;options=bold>Fetching NPM dependencies</>');
        $npmProcess = new Process(sprintf(
            'cd %s && %s install --production',
            $victoireUIConfigPath,
            $yarnPath
        ));

        try {
            $npmProcess->mustRun();

            echo $npmProcess->getOutput();
        } catch (ProcessFailedException $e) {
            $output->writeln(sprintf('<error>%s</error>', 'Did you installed npm or yarn properly ?'));
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }

        $output->writeln(sprintf('<info>%s</info>', $npmProcess->getOutput()));
        $output->writeln('<fg=green>Ok</> npm Assets fetched');
    }
}
