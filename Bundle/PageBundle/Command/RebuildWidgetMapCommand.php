<?php
namespace Victoire\Bundle\PageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Rebuild the widget map for a given VictoireCMS view
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
            ->setName('victoire:view:widgetMap:rebuild')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'The id of the view to rebuild')
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Should we recompute all the widget maps ?'
            )
            ->setDescription('Rebuild the view\'s widget map')
            ->setHelp(<<<EOT
The <info>victoire:view:widgetMap:rebuild</info> command helps you to generate a view WidgetMap.

<info>php app/console victoire:view:widgetMap:rebuild --id={id}</info>

or

<info>php app/console victoire:view:widgetMap:rebuild --all</info>
EOT
        );
    }

    /**
     * @param InputInterface  $input  The params the view gave
     * @param OutputInterface $output The output to display some things
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $all = (true === $input->getOption('all'));
        if ($all && null !== $input->getOption('id')) {
            throw new \InvalidArgumentException('You can pass either the --all option or the --id={id} param (but not both simultaneously).');
        }

        // Initialize data
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        if ($input->getOption('id')) {
            $output->writeln('<info>Get view "' . $input->getOption('id') . '"</info>');
            $views = array();
            $views[] = $view = $em->getRepository('VictoireCoreBundle:View')->find($input->getOption('id'));
            if (!$view) {
                $output->writeln('<error>View not found. Are you sure the view exists in db ?</error>');

                return false;
            }
            //TODO throw exception if not found
        } elseif ($input->getOption('all')) {
            $output->writeln('<info>Get all views </info>');
            $views = $em->getRepository('VictoireCoreBundle:View')->findAll();
        } else {
            throw new \InvalidArgumentException('You should at least pass one id or use the --all option to rebuild all the views.');
        }

        // Views
        $widgetMapBuilder = $this->getContainer()->get('view.widgetMap.builder');
        $cpt = 0;
        $output->writeln('<info>Starting process</info>');
        foreach ($views as $view) {
            // Build tree
            $output->writeln(sprintf('<comment>+++ Build widget map for view #%s : %s... </comment>', $view->getId(), $view->getSlug()));

            $widgetMapBuilder->removeMissingWidgets($view);
            $widgetMapBuilder->removeDuplicateWidgetLegacy($view);

            $em->persist($view);

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
