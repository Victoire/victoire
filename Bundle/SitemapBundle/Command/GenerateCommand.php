<?php

namespace Victoire\Bundle\SitemapBundle\Command;

use Predis\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\SitemapBundle\Domain\Export\SitemapExportHandler;

class GenerateCommand extends Command
{
    protected $redis;
    protected $handler;
    protected $locales;

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
        $this->setName('victoire:sitemap:generate')
            ->addOption('locale', 'l', InputOption::VALUE_OPTIONAL, 'The locale you want to generate', 'all')
            ->setDescription('Generate sitemap in cache');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Generating sitemap for %s locale(s)...</info>', $input->getOption('locale')));
        foreach ($this->getLocalesFromInput($input) as $locale) {
            $output->write(sprintf('<info>♻ %s </info>', $locale));
            $pages = $this->handler->handle($locale);
            $this->redis->set("sitemap.$locale", $this->handler->serialize($pages));
            $output->writeln('<info>🎉</info>');
        }
    }

    protected function getLocalesFromInput(InputInterface $input)
    {
        $locales = $this->locales;
        $locale = $input->getOption('locale');
        if ($locale != 'all') {
            //validate the given locale is a valid locale
            if (!array_search($locale, $locales)) {
                throw new \Exception(sprintf(
                        'The given locale %s doesn\'t exist (%s)',
                        $locale,
                        implode($locales))
                );
            }
            $locales = [$locale];
        }

        return $locales;
    }
}
