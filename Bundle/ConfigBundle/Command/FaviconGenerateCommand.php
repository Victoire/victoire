<?php

namespace Victoire\Bundle\ConfigBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Victoire\Bundle\ConfigBundle\Entity\GlobalConfig;
use Victoire\Bundle\ConfigBundle\Favicon\FaviconGenerator;

class FaviconGenerateCommand extends ContainerAwareCommand
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
            ->setName('victoire:favicons:generate')
            ->addArgument('target', InputArgument::OPTIONAL, 'The target directory', 'web')
            ->addArgument(
                'configPath',
                InputArgument::OPTIONAL,
                'The target directory',
                'faviconConfig.json'
            )
            ->addOption('realFaviconPath',
                    'real',
                    InputArgument::OPTIONAL,
                    'The cli-real-favicon generator full path',
                    'vendor/victoire/victoire/Bundle/UIBundle/Resources/config/node_modules/cli-real-favicon/real-favicon.js'
            )
            ->setDescription('Generate icons according to global config');
    }

    /**
     * Generate favicons thanks to realfavicon generator.
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
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        try {
            $output->writeln('<fg=white;bg=cyan;options=bold>Generating Favicon</>');
            $generator = $this->getContainer()->get(FaviconGenerator::class);
            $files = $generator->generate(
                $entityManager->getRepository(GlobalConfig::class)->find(1)
            );
            $output->writeln(sprintf('<comment>%s</comment><info>%s</info>', 'Command line : ', $generator->process->getCommandLine()));
            $output->writeln(sprintf('<info>%s</info>', $generator->process->getOutput()));
            $output->writeln(sprintf('<fg=green>Ok</> The favicons have been generated in <info>%s</info> directory', $generator->target));
            foreach ($files as $file) {
                $output->writeln(sprintf('<comment>- %s</comment>', $file));
            }
        } catch (ProcessFailedException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
