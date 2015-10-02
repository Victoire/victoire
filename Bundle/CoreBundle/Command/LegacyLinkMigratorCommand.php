<?php

namespace Victoire\Bundle\CoreBundle\Command;

use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\CoreBundle\Entity\Link;

class LegacyLinkMigratorCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:legacy:linkMigrator')
            ->setDescription('migrate old LinkTrait architecture into Link entity');
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
        $progress = $this->getHelperSet()->get('progress');
        $progress->setProgressCharacter('V');
        $progress->setEmptyBarCharacter('-');

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($entityManager);
        $metadatas = $cmf->getAllMetadata();
        $classes = [];
        $output->writeln('<info>Parse every classes to know which ones are related to Link</info>');
        $progress->start($output, count($metadatas));
        foreach ($metadatas as $key => $metadata) {
            $progress->advance();
            if ($metadata->hasAssociation('link')) {
                $association = $metadata->getAssociationMapping('link');
                if ('Victoire\Bundle\CoreBundle\Entity\Link' === $association['targetEntity']) {
                    $classes[] = $metadata;
                }
            }
        }
        $progress->finish();

        $counter = 0;
        if (count($classes)) {
            $output->writeln('<info>Let\'s migrate</info>');
            $progress->start($output, count($classes));
            foreach ($classes as $class) {
                $progress->advance();
                //get the full universe of entities thanks to the entity repository
                $objects = $entityManager->getRepository($class->name)->findAll();
                foreach ($objects as $object) {
                    if (!$object->hasLink()) {
                        //Create a Link according to the legacy link trait properties
                        $link = new Link();
                        $object->setLink($link);
                        //fill the values
                        $link->setUrl($object->url);
                        $link->setTarget($object->target);
                        $link->setRoute($object->route);
                        $link->setRouteParameters($object->routeParameters);
                        $link->setPage($object->page);
                        $link->setLinkType($object->linkType);
                        $link->setAttachedWidget($object->attachedWidget);
                        $link->setAnalyticsTrackCode($object->analyticsTrackCode);
                        //persist the new link and the relation
                        $entityManager->persist($object);
                        $entityManager->persist($link);
                        $entityManager->flush();
                        $counter++;
                    }
                }
            }
            $progress->finish();
            $output->writeln(sprintf('<comment>Ok, %s records migrated !</comment>', $counter));
        }

        if (0 == $counter) {
            $output->writeln('<comment>Nothing to do...</comment>');
        }
    }
}
