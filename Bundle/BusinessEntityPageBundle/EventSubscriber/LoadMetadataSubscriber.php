<?php

namespace Victoire\Bundle\BusinessEntityPageBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;

/**
 * This class listen Mission Entity changes, update its status and create or update ScheduledEvent.
 */
class LoadMetadataSubscriber implements EventSubscriber
{
    /**
     * bind to LoadClassMetadata method
     *
     * @return array The list of events
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
        );
    }

    /**
     * Insert DiscriminatorMap in the class metadatas
     * @param string $class
     */
    public function loadClassMetadata($class)
    {
        if ($class->getClassMetadata()->name === 'Victoire\Bundle\PageBundle\Entity\BasePage') {
            $class->getClassMetadata()->discriminatorMap[BusinessEntityPagePattern::TYPE] = 'Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern';
        }

        if ($class->getClassMetadata()->name === 'Victoire\Bundle\PageBundle\Entity\BasePage') {
            $class->getClassMetadata()->discriminatorMap[BusinessEntityPage::TYPE] = 'Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage';
        }
    }
}
