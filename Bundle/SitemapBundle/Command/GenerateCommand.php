<?php

namespace Victoire\Bundle\SitemapBundle\Command;

use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\SitemapBundle\Domain\Export\SitemapExportHandler;

class GenerateCommand extends ContainerAwareCommand
{
    private $redis;
    private $handler;
    private $locales;

    public function __construct(Client $redis, SitemapExportHandler $handler, array $locales)
    {
        parent::__construct();

        $this->redis = $redis;
        $this->handler = $handler;
        $this->locales = $locales;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('victoire:generate:sitemap')
            ->setDescription('Generate sitemap in cache');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->locales as $locale) {
            $pages = $this->handler->handle($locale);
            $this->redis->set("sitemap.$locale", $this->handler->serialize($pages));
        }
    }
}
