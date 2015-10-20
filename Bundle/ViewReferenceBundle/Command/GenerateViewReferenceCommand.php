<?php

namespace Victoire\Bundle\ViewReferenceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateViewReferenceCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:viewReference:generate')
            ->setAliases(['victoire:generate:view-cache'])
            ->setDescription('write view references in a xml cache file');
    }

    /**
     * Read declared business entities and BusinessEntityPatternPages to generate their urls.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $viewsReferences = $this->getContainer()->get('victoire_core.view_helper')->buildViewsReferences();
        $this->getContainer()->get('victoire_view_reference.cache.writer')->generateXml($viewsReferences);
    }
}
