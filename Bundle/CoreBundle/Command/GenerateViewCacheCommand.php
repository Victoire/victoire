<?php
namespace Victoire\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateViewCacheCommand extends ContainerAwareCommand
{

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:generate:view-cache')
            ->setDescription('write view references in a xml cache file');
    }

    /**
     * Read declared business entities and BusinessEntityPatternPages to generate their urls
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $views = $this->getContainer()->get('doctrine.orm.entity_manager')->createQuery("SELECT v FROM VictoireCoreBundle:View v")->getResult();
        $viewsReferences = $this->getContainer()->get('victoire_core.view_helper')->buildViewsReferences($views);
        $this->getContainer()->get('victoire_core.view_cache_helper')->write($viewsReferences);
    }
}
