<?php
namespace Victoire\Bundle\PageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\CoreBundle\Generator\WidgetGenerator;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Rebuild the widget map for a given VictoireCMS page
 */
class RebuildWidgetMapCommand extends ContainerAwareCommand
{

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:page:widgetMap:rebuild')
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'The url of the page/template to rebuild')
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Should we recompute all the widget maps ?'
            )
            ->setDescription('Rebuild the page\'s widget map')
            ->setHelp(<<<EOT
The <info>victoire:page:widgetMap:rebuild</info> command helps you to generate a page WidgetMap.

<info>php app/console victoire:page:widgetMap:rebuild --url=my-page</info>

or

<info>php app/console victoire:page:widgetMap:rebuild --all</info>
EOT
        );
    }

    /**
     * @param InputInterface  $input  The params the page gave
     * @param OutputInterface $output The output to display some things
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $all = (true === $input->getOption('all'));
        if ($all && null !== $input->getOption('url')) {
            throw new \InvalidArgumentException('You can pass either the --all option or the --url={url} param (but not both simultaneously).');
        }

        // Initialize data
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        if ($input->getOption('url')) {
            $output->writeln('<info>Get page "' . $input->getOption('url') . '"</info>');
            $pages = array();
            $pages[] = $page = $em->getRepository('VictoirePageBundle:BasePage')->findOneByUrl($input->getOption('url'));
            if (!$page) {
                $output->writeln('<error>Page not found. Are you sure the page exists in db ?</error>');
                return false;
            }
            //TODO throw exception if not found
        } elseif ($input->getOption('all')) {
            $output->writeln('<info>Get all pages </info>');
            $pages = $em->getRepository('VictoirePageBundle:BasePage')->findAll();
        } else {
            throw new \InvalidArgumentException('You should at least pass one url or use the --all option to rebuild all the pages.');
        }

        // Pages
        $widgetMapBuilder = $this->getContainer()->get('page.widgetMap.builder');
        $cpt = 0;
        $output->writeln('<info>Starting process</info>');
        foreach ($pages as $page) {
            // Build tree
            $output->writeln(sprintf('<comment>+++ Build widget map for page #%s : %s... </comment>', $page->getId(), $page->getSlug()));
            $page->setWidgetMap($widgetMapBuilder->build($page));
            $em->persist($page);

            // Flush every 100 queries
            if (0 === $cpt % 100) {
                $em->flush();
            }
            $cpt++;
        }

        $em->flush();
        $output->writeln(sprintf('<comment>Finish : %s done</comment>', $cpt));
    }
}
