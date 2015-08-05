<?php
namespace Victoire\Bundle\WidgetMapBundle\Command;

use Doctrine\DBAL\Types\Type;
use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Repository\ViewRepository;
use Victoire\Bundle\WidgetBundle\Generator\WidgetGenerator;

/**
 * Create a new Widget for VictoireCMS
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
            ->setName('victoire:widgetMap:rebuild')
            ->addArgument('viewId', InputArgument::OPTIONAL, 'Which view do you want to rebuild the widgetMap ?')
            ->setDescription('Rebuild the WidgetMap of the given view');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $entityManager->getRepository('VictoireCoreBundle:View');
        $params = array();
        if ($input->getArgument('viewId')) {
            $params = array('id' => $input->getArgument('viewId'));
        }

        $progress = $this->getHelperSet()->get('progress');
        $progress->setProgressCharacter('V');
        $progress->setEmptyBarCharacter('-');

        /** @var View[] $views */
        $views = $repo->findBy($params);
        $output->writeln(sprintf('<info>%s views to rebuild.</info>', count($views)));
        $counter = 0;
        $progress->start($output, count($views));
        foreach ($views as $view) {
            $progress->advance();
            $counter++;
            $this->getContainer()->get('victoire_widget_map.builder')->rebuild($view);

            $entityManager->persist($view);
            $entityManager->flush();
        }
        $progress->finish();

        if (0 == $counter) {
            $output->writeln('<comment>Nothing to do...</comment>');
        } else {
            $output->writeln(sprintf('<comment>Ok, %s widgetMaps built !</comment>', $counter));
        }
    }
}
