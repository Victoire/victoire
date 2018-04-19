<?php

namespace Victoire\Bundle\ConfigBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\ConfigBundle\Entity\GlobalConfig;
use Victoire\Bundle\ConfigBundle\Favicon\FaviconConfigDumper;

class FaviconDumpConfigFileCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:favicons:dumpConfig')
            ->addArgument(
                'target',
                InputArgument::OPTIONAL,
                'The target directory',
                'faviconConfig.json'
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
        //Get services
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $globalConfig = $entityManager->getRepository(GlobalConfig::class)->find(1);

        try {
            $this->getContainer()->get(FaviconConfigDumper::class)->dump(
                $globalConfig,
                $input->getArgument('target')
            );
            $output->writeln(
                sprintf(
                    '<fg=green>Ok</> Favicon config file has been dumped in %s',
                    $input->getArgument('target')
                )
            );
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
