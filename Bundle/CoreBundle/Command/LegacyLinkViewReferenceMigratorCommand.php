<?php

namespace Victoire\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\CoreBundle\Entity\Link;

class LegacyLinkViewReferenceMigratorCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:legacy:linkViewReferenceMigrator')
            ->setDescription('migrate link target from page object to viewReference cached id');
    }

    /**
     * Get all links and transform page relation into viewReference.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progress = $this->getHelperSet()->get('progress');
        $progress->setProgressCharacter('V');
        $progress->setEmptyBarCharacter('-');

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $links = $entityManager->getRepository("Victoire\Bundle\CoreBundle\Entity\Link")->findAll();

        $progress->start($output, count($links));
        $counter = 0;
        foreach ($links as $link) {
            $progress->advance();
            if ($link->getLinkType() == 'page' && $page = $link->getPage()) {
                $viewReference = $this->getContainer()->get('victoire_core.helper.view_reference_helper')->getViewReferenceId($page);
                $link->setViewReference($viewReference);
                $link->setLinkType('viewReference');
            }
            $counter++;
        }

        $entityManager->flush();

        $progress->finish();
        $output->writeln(sprintf('<comment>Ok, %s records migrated !</comment>', $counter));

        if (0 == $counter) {
            $output->writeln('<comment>Nothing to do...</comment>');
        }
    }
}
