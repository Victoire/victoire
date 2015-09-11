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
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Victoire\Bundle\CoreBundle\Helper\ViewUrlHelper;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Helper\UserCallableHelper;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;

/**
 * This class listen Page Entity changes.
 */
class PageSubscriber implements EventSubscriber
{
    protected $router;
    protected $userClass;
    protected $userCallable;
    protected $viewCacheHelper;
    protected $viewUrlHelper;

    /**
     * Constructor
     * @param Router             $router          @router
     * @param UserCallableHelper $userCallable    @victoire_page.user_callable
     * @param string             $userClass       %victoire_core.user_class%
     * @param ViewCacheHelper    $viewCacheHelper @victoire_core.view_cache_helper
     * @param ViewUrlHelper      $viewUrlHelper   @victoire_core.view_url_helper
     */
    public function __construct($router, $userCallable, $userClass, $viewCacheHelper, $viewUrlHelper)
    {
        $this->router          = $router;
        $this->userClass       = $userClass;
        $this->userCallable    = $userCallable;
        $this->viewCacheHelper = $viewCacheHelper;
        $this->viewUrlHelper = $viewUrlHelper;
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
            'onFlush',
            'postPersist',
        );
    }

    /**
     * Insert enabled widgets in base widget DiscriminatorMap
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
            $metadata->mapManyToOne(array(
                'fieldName'    => 'author',
                'targetEntity' => $this->userClass,
                'cascade'      => array('persist'),
                'inversedBy'   => 'pages',
                'joinColumns' => array(
                    array(
                        'name' => 'author_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'SET NULL',
                    ),
                ),
            ));
        }

        // if $pages property exists, add the inversed side on User
        if ($metadata->name === $this->userClass && property_exists($this->userClass, 'pages')) {
            $metaBuilder->addOneToMany('pages', 'Victoire\Bundle\PageBundle\Entity\View', 'author');
        }
    }

    /**
     * This method is called on flush
     * @param OnFlushEventArgs $eventArgs The flush event args.
     *
     * @return void
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $eventArgs->getEntityManager();
        /** @var UnitOfWork $uow */
        $uow = $entityManager->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof WebViewInterface) {
                $computeUrl = ((array_key_exists('slug', $uow->getEntityChangeSet($entity)) //the slug of the page has been modified
                            || array_key_exists('staticUrl', $uow->getEntityChangeSet($entity)) //the static url of the page has been modified
                            || array_key_exists('parent', $uow->getEntityChangeSet($entity)))
                            ); //the parent has been modified
                if ($computeUrl) {
                    $this->viewUrlHelper->buildUrl($entity, $uow, $entityManager) ;
                }
                $meta = $entityManager->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($meta, $entity);
                $entity->setAuthor($this->userCallable->getCurrentUser());
                if ($entity instanceof BusinessEntityPagePattern) {
                    $this->updateCache($entity);
                }

            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof WebViewInterface) {
                $computeUrl = ((array_key_exists('slug', $uow->getEntityChangeSet($entity)) //the slug of the page has been modified
                            || array_key_exists('staticUrl', $uow->getEntityChangeSet($entity)) //the static url of the page has been modified
                            || array_key_exists('parent', $uow->getEntityChangeSet($entity)))
                            ); //the parent has been modified
                if ($computeUrl) {
                    $this->viewUrlHelper->buildUrl($entity, $uow, $entityManager);
                    $meta = $entityManager->getClassMetadata(get_class($entity));
                    $uow->computeChangeSet($meta, $entity);
                    $this->updateCache($entity);
                }
            }
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof View) {
            $this->updateCache($entity);
        }
    }

    /**
     * There is changes in the Page, we have to update the page references cache file
     * @param BasePage $page the page
     *
     * @return void
     */
    protected function updateCache(View $page)
    {
        if ($page instanceof BusinessEntityPage) {
            $this->viewCacheHelper->update($page, $page->getBusinessEntity());
        } else {
            $this->viewCacheHelper->update($page);
        }
    }
}
