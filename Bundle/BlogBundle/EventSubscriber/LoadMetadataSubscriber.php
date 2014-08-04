<?php
namespace Victoire\Bundle\BlogBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\BlogBundle\Entity\Blog;

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
        if ($class->getClassMetadata()->name === 'Victoire\Bundle\CoreBundle\Entity\View') {
            $class->getClassMetadata()->discriminatorMap[Article::TYPE] = 'Victoire\Bundle\BlogBundle\Entity\Article';
        }

        if ($class->getClassMetadata()->name === 'Victoire\Bundle\CoreBundle\Entity\View') {
            $class->getClassMetadata()->discriminatorMap[Blog::TYPE] = 'Victoire\Bundle\BlogBundle\Entity\Blog';
        }
    }
}
