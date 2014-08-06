<?php

namespace Victoire\Bundle\TemplateBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Victoire\Bundle\TemplateBundle\Entity\Template;

/**
 * This class listen View Entity changes.
 */
class ViewSubscriber implements EventSubscriber
{

    /**
     * bind to LoadClassMetadata method
     *
     * @return array The subscribed events
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata'
        );
    }

    /**
     * Insert enabled widgets in base widget DiscriminatorMap
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {

        $metadatas = $eventArgs->getClassMetadata();
        if ($metadatas->name === 'Victoire\Bundle\CoreBundle\Entity\View') {
            $metadatas->discriminatorMap[Template::TYPE] = 'Victoire\Bundle\TemplateBundle\Entity\Template';
        }
    }
}
