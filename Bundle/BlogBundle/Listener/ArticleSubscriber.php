<?php

namespace Victoire\Bundle\BlogBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\UnitOfWork;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\PageBundle\Helper\UserCallableHelper;

class ArticleSubscriber implements EventSubscriber
{
    protected $userClass;
    protected $userCallableHelper;

    /**
     * Constructor.
     *
     * @param UserCallableHelper $userCallableHelper @victoire_page.user_callable
     * @param string             $userClass          %victoire_core.user_class%
     *
     * @internal param ViewReferenceBuilder $urlBuilder @victoire_view_reference.builder
     */
    public function __construct(
        UserCallableHelper $userCallableHelper,
        $userClass
    ) {
        $this->userClass = $userClass;
        $this->userCallableHelper = $userCallableHelper;
    }

    /**
     * bind to LoadClassMetadata method.
     *
     * @return string[] The subscribed events
     */
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata',
            'onFlush',
        ];
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $eventArgs->getEntityManager();
        /** @var UnitOfWork $uow */
        $uow = $entityManager->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Article) {
                $entity->setAuthor($this->userCallableHelper->getCurrentUser());
            }
        }
    }

    /**
     * Insert enabled widgets in base widget DiscriminatorMap.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        //set a relation between Page and User to define the page author
        $metaBuilder = new ClassMetadataBuilder($metadata);

        //Add author relation on view
        if ($this->userClass && $metadata->name === 'Victoire\Bundle\BlogBundle\Entity\Article') {
            $metadata->mapManyToOne([
                'fieldName'    => 'author',
                'targetEntity' => $this->userClass,
                'cascade'      => ['persist'],
                'inversedBy'   => 'articles',
                'joinColumns'  => [
                    [
                        'name'                 => 'author_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'SET NULL',
                    ],
                ],
            ]);
        }

        // if $article's property exists, add the inversed side on User
        if ($metadata->name === $this->userClass && property_exists($this->userClass, 'articles')) {
            $metaBuilder->addOneToMany('articles', 'Victoire\Bundle\BlogBundle\Entity\Article', 'author');
        }
    }
}
