<?php

namespace Victoire\Bundle\ViewReferenceBundle\EventSubscriber;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Builder\BusinessPageBuilder;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Cache\Xml\ViewReferenceXmlCacheDriver;
use Victoire\Bundle\ViewReferenceBundle\Cache\Xml\ViewReferenceXmlCacheManager;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Provider\ViewReferenceProvider;

/**
 * Tracks if a slug changed and re-compute the view cache
 * ref: victoire_view_reference.event_subscriber.
 */
class ViewReferenceSubscriber implements EventSubscriber
{
    protected $viewCacheManager;
    protected $viewCacheDriver;
    protected $businessPageBuilder;
    protected $viewReferenceProvider;
    protected $viewReferenceHelper;
    protected $insertedEntities = [];
    protected $updatedEntities = [];
    protected $deletedEntities = [];
    protected $flushedEntities = [];

    /**
     * @param ViewReferenceXmlCacheManager $viewCacheManager
     * @param ViewReferenceXmlCacheDriver $viewCacheDriver
     * @param BusinessPageBuilder $businessPageBuilder
     * @param ViewReferenceProvider $viewReferenceProvider
     * @param ViewReferenceHelper $viewReferenceHelper
     * @param BusinessEntityHelper $businessEntityHelper
     */
    public function __construct(ViewReferenceXmlCacheManager $viewCacheManager,
                                ViewReferenceXmlCacheDriver  $viewCacheDriver,
                                BusinessPageBuilder          $businessPageBuilder,
                                ViewReferenceProvider        $viewReferenceProvider,
                                ViewReferenceHelper          $viewReferenceHelper,
                                BusinessEntityHelper         $businessEntityHelper
    ) {
        $this->viewCacheManager = $viewCacheManager;
        $this->viewCacheDriver = $viewCacheDriver;
        $this->businessPageBuilder = $businessPageBuilder;
        $this->viewReferenceProvider = $viewReferenceProvider;
        $this->viewReferenceHelper = $viewReferenceHelper;
        $this->businessEntityHelper = $businessEntityHelper;
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
            Events::postFlush,
            Events::postUpdate,
        ];
    }

    /**
     * Will rebuild url if needed and update cache.
     *
     * @param PostFlushEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $eventArgs->getEntityManager();
        /** @var UnitOfWork $uow */
        $uow = $entityManager->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof BusinessTemplate) {
                if (((array_key_exists('slug', $uow->getEntityChangeSet($entity)) //the slug of the page has been modified
                    || array_key_exists('staticUrl', $uow->getEntityChangeSet($entity))
                    || array_key_exists('parent', $uow->getEntityChangeSet($entity))
                    || array_key_exists('template', $uow->getEntityChangeSet($entity)))
                )) {
                    // Get BusinessPages of the given BusinessTemplate
                    $inheritors = $entityManager->getRepository('Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage')->findBy(array('Template' => $entity));
                    foreach ($inheritors as $instance) {
                        $this->updateBusinessPageUrl($instance, $entityManager, $uow);
                    }
                }
            }
        }
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $uow = $eventArgs->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            //If entity is a BusinessEntity or just a view
            if ($this->businessEntityHelper->findByEntityInstance($entity) || $entity instanceof View) {
                $this->flushedEntities[] = $entity;
                $this->insertedEntities[] = $entity;
                break;
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            //If entity is a BusinessEntity or just a view
            if ($this->businessEntityHelper->findByEntityInstance($entity) || $entity instanceof View) {
                $this->updatedEntities[] = $entity;
                $this->flushedEntities[] = $entity;
                break;
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            //If entity is a BusinessEntity or just a view
            if ($this->businessEntityHelper->findByEntityInstance($entity) || $entity instanceof View) {
                $this->deletedEntities[] = $entity;
                $this->flushedEntities[] = $entity;
                break;
            }
        }
    }

    /**
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if (count($this->flushedEntities)) {
            $this->rebuildViewsReferenceCache($eventArgs);
        }
    }

    /**
     * Manage urls.
     *
     * @param View $page
     *
     * @return void
     */
    protected function updateBusinessPageUrl(BusinessPage $page, EntityManager $em, UnitOfWork $uow)
    {
        $oldSlug = $page->getSlug();
        $staticUrl = $page->getStaticUrl();
        $computedPage = $this->businessPageBuilder->generateEntityPageFromTemplate($page->getTemplate(), $page->getBusinessEntity(), $em);
        $newSlug = $computedPage->getSlug();

        if ($staticUrl) {
            $staticUrl = preg_replace('/'.$oldSlug.'/', $newSlug, $staticUrl);
            $page->setStaticUrl($staticUrl);
        }
        $page->setSlug($newSlug);
        $meta = $em->getClassMetadata(get_class($page));
        $em->persist($page);
        $uow->computeChangeSet($meta, $page);
    }

    /**
     * @param PostFlushEventArgs $eventArgs
     */
    protected function rebuildViewsReferenceCache(PostFlushEventArgs $eventArgs)
    {
        //Rebuild viewsReferences xml cache
        /** @var EntityManager $entityManager */
        $entityManager = $eventArgs->getEntityManager();
        $viewRepository = $entityManager->getRepository('VictoireCoreBundle:View');
        $tree = $viewRepository->getRootNodes();

        $insertFunc = function($views, $toInsert) use (&$insertFunc) {
            foreach ($views as $_view) {
                if ($toInsert->getParent() === $_view) {
                    $_view->addChild($toInsert);
                    break;
                }
                $insertFunc($_view->getChildren(), $toInsert);
            }
        };

        foreach ($this->insertedEntities as $_insertedEntity) {
            if ($_insertedEntity instanceof View) {
                $insertFunc($tree, $_insertedEntity);
            }
        }

        $views = $this->viewReferenceProvider->getReferencableViews($tree, $entityManager);
        $this->viewReferenceHelper->buildViewReferenceRecursively($views, $entityManager);

        $this->viewCacheDriver->writeFile(
            $this->viewCacheManager->generateXml($views)
        );
        //End of viewsReferences xml cache rebuild
    }
}
