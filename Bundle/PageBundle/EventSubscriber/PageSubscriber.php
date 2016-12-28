<?php

namespace Victoire\Bundle\PageBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\UnitOfWork;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Victoire\Bundle\APIBusinessEntityBundle\Resolver\APIBusinessEntityResolver;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\ORMBusinessEntityBundle\Entity\ORMBusinessEntity;
use Victoire\Bundle\PageBundle\Helper\UserCallableHelper;
use Victoire\Bundle\ViewReferenceBundle\Builder\ViewReferenceBuilder;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * This class listen Page Entity changes.
 */
class PageSubscriber implements EventSubscriber
{
    protected $router;
    protected $userClass;
    protected $userCallableHelper;
    protected $urlBuilder;
    protected $viewReferenceRepository;
    /**
     * @var APIBusinessEntityResolver
     */
    private $apiBusinessEntityResolver;

    /**
     * Constructor.
     *
     * @param Router                    $router             @router
     * @param UserCallableHelper        $userCallableHelper @victoire_page.user_callable
     * @param string                    $userClass          %victoire_core.user_class%
     * @param ViewReferenceBuilder      $viewReferenceBuilder
     * @param ViewReferenceRepository   $viewReferenceRepository
     * @param TranslatableListener      $translatableListener
     *
     * @param APIBusinessEntityResolver $apiBusinessEntityResolver
     *
     * @internal param ViewReferenceBuilder $urlBuilder @victoire_view_reference.builder
     */
    public function __construct(
        Router $router,
        UserCallableHelper $userCallableHelper,
        $userClass,
        ViewReferenceBuilder $viewReferenceBuilder,
        ViewReferenceRepository $viewReferenceRepository,
        TranslatableListener $translatableListener,
        APIBusinessEntityResolver $apiBusinessEntityResolver
    ) {
        $this->router = $router;
        $this->userClass = $userClass;
        $this->userCallableHelper = $userCallableHelper;
        $this->viewReferenceBuilder = $viewReferenceBuilder;
        $this->viewReferenceRepository = $viewReferenceRepository;
        $this->translatableListener = $translatableListener;
        $this->apiBusinessEntityResolver = $apiBusinessEntityResolver;
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

    /**
     * If entity is a View
     * it will find the ViewReference related to the current view and populate its url.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $view = $eventArgs->getEntity();

        if ($view instanceof View) {
            $view->setReferences([$view->getCurrentLocale() => new ViewReference($view->getId())]);
            $viewReferences = $this->viewReferenceRepository->getReferencesByParameters([
                'viewId'     => $view->getId(),
                'templateId' => $view->getId(),
            ], true, false, 'OR');
            foreach ($viewReferences as $viewReference) {
                if ($viewReference->getLocale() === $view->getCurrentLocale()) {
                    if ($view instanceof WebViewInterface && $viewReference instanceof ViewReference) {
                        $view->setReference($viewReference, $viewReference->getLocale());
                        $view->setUrl($viewReference->getUrl());
                    }
                }
            }
            if ($view instanceof BusinessPage && $businessEntity = $view->getEntityProxy()->getBusinessEntity()) {
                $entityProxy = $view->getEntityProxy();
                $viewReference = $view->getReference();

                if ($businessEntity->getType() === ORMBusinessEntity::TYPE) {
                    $entity = $eventArgs->getEntityManager()->getRepository($businessEntity->getClass())
                        ->findOneBy(['id' => $viewReference->getEntityId()]);
                } else {
                    $entityProxy = new EntityProxy();
                    $entityProxy->setBusinessEntity($businessEntity);
                    $entityProxy->setRessourceId($viewReference->getEntityId());
                    $entity = $this->apiBusinessEntityResolver->getBusinessEntity($entityProxy);
                }

                $entityProxy->setEntity($entity);
            }
        }
    }
}
