<?php

namespace Victoire\Bundle\PageBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\ViewReferenceBundle\Cache\Xml\ViewReferenceXmlCacheRepository;
use Victoire\Bundle\PageBundle\Helper\UserCallableHelper;

/**
 * This class listen Page Entity changes.
 */
class PageSubscriber implements EventSubscriber
{
    protected $router;
    protected $userClass;
    protected $userCallableHelper;
    protected $urlBuilder;

    /**
     * Constructor.
     *
     * @param Router             $router             @router
     * @param UserCallableHelper $userCallableHelper @victoire_page.user_callable
     * @param string             $userClass          %victoire_core.user_class%
     * @param UrlBuilder         $urlBuilder         @victoire_core.url_builder
     */
    public function __construct($router, $userCallableHelper, $userClass, $urlBuilder)
    {
        $this->router = $router;
        $this->userClass = $userClass;
        $this->userCallableHelper = $userCallableHelper;
        $this->urlBuilder = $urlBuilder;
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
            'postLoad',
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
            if ($entity instanceof View) {
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
        if ($this->userClass && $metadata->name === 'Victoire\Bundle\CoreBundle\Entity\View') {
            $metadata->mapManyToOne([
                'fieldName'    => 'author',
                'targetEntity' => $this->userClass,
                'cascade'      => ['persist'],
                'inversedBy'   => 'pages',
                'joinColumns'  => [
                    [
                        'name'                 => 'author_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'SET NULL',
                    ],
                ],
            ]);
        }

        // if $pages property exists, add the inversed side on User
        if ($metadata->name === $this->userClass && property_exists($this->userClass, 'pages')) {
            $metaBuilder->addOneToMany('pages', 'Victoire\Bundle\CoreBundle\Entity\View', 'author');
        }
    }

    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof View) {
            $entity->setReference(['id' => $entity->getId()]);
        }
        if ($entity instanceof WebViewInterface) {
            $entity->setUrl($this->urlBuilder->buildUrl($entity));
        }
    }
}
