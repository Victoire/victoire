<?php

namespace Victoire\Bundle\AnalyticsBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * This class listen Browse Event Entity changes.
 */
class BrowseEventSubscriber implements EventSubscriber
{
    protected $router;
    protected $userClass;

    /**
     * Constructor
     * @param string $userClass %victoire_core.user_class%
     */
    public function __construct($userClass)
    {
        $this->userClass = $userClass;
    }

    /**
     * bind to LoadClassMetadata method
     *
     * @return string[] The subscribed events
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
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

        //set a relation between Page and User to define the page author
        $metaBuilder = new ClassMetadataBuilder($metadatas);

        //Add author relation on BrowseEvent
        if ($this->userClass && $metadatas->name === 'Victoire\Bundle\AnalyticsBundle\Entity\BrowseEvent') {
            $metadatas->mapManyToOne(array(
                'fieldName'    => 'author',
                'targetEntity' => $this->userClass,
                'cascade'      => array('persist'),
                'joinColumns' => array(
                    array(
                        'name' => 'author_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'SET NULL',
                    ),
                ),
            ));
        }
    }
}
