<?php
namespace Victoire\Bundle\PageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PageCacheCommand extends ContainerAwareCommand
{

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:page:write-cache')
            ->setDescription('write page references in a xml cache file');
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
        $pages = $this->getContainer()->get('victoire_page.page_helper')->getAllPages();
        $this->getContainer()->get('victoire_page.page_cache_helper')->writeCache($pages);
    }

}
