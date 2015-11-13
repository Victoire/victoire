<?php

namespace Victoire\Bundle\BusinessEntityBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\DependencyInjection\Container;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Builder\BusinessPageBuilder;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\BusinessPageBundle\Repository\BusinessPageRepository;
use Victoire\Bundle\ViewReferenceBundle\Cache\Xml\ViewReferenceXmlCacheDriver;
use Victoire\Bundle\ViewReferenceBundle\Cache\Xml\ViewReferenceXmlCacheManager;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Provider\ViewReferenceProvider;

class BusinessEntitySubscriber implements EventSubscriber
{
    protected $viewCacheManager;
    protected $viewCacheDriver;
    protected $businessPageBuilder;
    protected $viewReferenceProvider;
    protected $viewReferenceHelper;

    /**
     * @param ViewReferenceXmlCacheManager $viewCacheManager
     * @param ViewReferenceXmlCacheDriver $viewCacheDriver
     * @param BusinessPageBuilder $businessPageBuilder
     * @param ViewReferenceProvider $viewReferenceProvider
     * @param ViewReferenceHelper $viewReferenceHelper
     * @param BusinessEntityHelper $businessEntityHelper
     * @param BusinessPageHelper $businessPageHelper
     */
    public function __construct(ViewReferenceXmlCacheManager $viewCacheManager,
                                ViewReferenceXmlCacheDriver  $viewCacheDriver,
                                BusinessPageBuilder          $businessPageBuilder,
                                ViewReferenceProvider        $viewReferenceProvider,
                                ViewReferenceHelper          $viewReferenceHelper,
                                BusinessEntityHelper         $businessEntityHelper,
                                BusinessPageHelper           $businessPageHelper
    ) {
        $this->viewCacheManager = $viewCacheManager;
        $this->viewCacheDriver = $viewCacheDriver;
        $this->businessPageBuilder = $businessPageBuilder;
        $this->viewReferenceProvider = $viewReferenceProvider;
        $this->viewReferenceHelper = $viewReferenceHelper;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->businessPageHelper = $businessPageHelper;
    }

    /**
     * bind to LoadClassMetadata method.
     *
     * @return string[] The subscribed events
     */
    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'postUpdate',
            'postFlush',
        ];
    }

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->updateBusinessPagesAndRegenerateCache($eventArgs);
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->updateBusinessPagesAndRegenerateCache($eventArgs);
    }

    /**
     * then refresh viewsReference
     * @param LifecycleEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $viewsHierarchy = $entityManager->getRepository('VictoireCoreBundle:View')->getRootNodes();
        $views = $this->viewReferenceProvider->getReferencableViews($viewsHierarchy, $entityManager);

        $this->viewReferenceHelper->buildViewReferenceRecursively($views, $entityManager);
        $this->viewCacheDriver->writeFile(
            $this->viewCacheManager->generateXml($views)
        );
    }

    /**
     *
     * get BusinessTemplate concerned by this entity (if so)
     * then get BusinessPages
     * for each BusinessPage, update its slug according to the new slug
     * then refresh viewsReference
     *
     * @param LifecycleEventArgs $eventArgs
     * @throws \Exception
     */
    public function updateBusinessPagesAndRegenerateCache(LifecycleEventArgs $eventArgs)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);

        if ($businessEntity) {
            $businessTemplates = $entityManager->getRepository('VictoireBusinessPageBundle:BusinessTemplate')->findPagePatternByBusinessEntity($businessEntity);
            foreach ($businessTemplates as $businessTemplate) {
                if ($this->businessPageHelper->isEntityAllowed($businessTemplate, $entity, $entityManager)) {
                    /** @var BusinessPageRepository $bepRepo */
                    $bepRepo = $entityManager->getRepository('VictoireBusinessPageBundle:BusinessPage');

                    $virtualBusinessPage = $this->businessPageBuilder->generateEntityPageFromTemplate(
                        $businessTemplate,
                        $entity,
                        $entityManager
                    );
                    // Get the BusinessPage if exists for the given entity
                    /** @var BusinessPage $businessPage */
                    $businessPage = $bepRepo->findPageByBusinessEntityAndPattern(
                        $businessTemplate,
                        $entity,
                        $businessEntity
                    );
                    // If there is diff between persisted BEP and computed, persist the change
                    $scheduledForRemove = false;
                    foreach ($eventArgs->getEntityManager()->getUnitOfWork()->getScheduledEntityDeletions() as $deletion) {
                        if (get_class($deletion) == get_class($businessPage)
                            && $deletion->getId() === $businessPage->getId()
                        ) {
                            $scheduledForRemove = true;
                        }
                    }

                    if ($businessPage && !$scheduledForRemove) {
                        $oldSlug = $businessPage->getSlug();
                        $newSlug = $entity->getSlug();
                        $staticUrl = $businessPage->getStaticUrl();

                        if ($staticUrl) {
                            $staticUrl = preg_replace('/' . $oldSlug . '/', $newSlug, $staticUrl);
                            $businessPage->setStaticUrl($staticUrl);
                        }

                        $businessPage->setName($virtualBusinessPage->getName());
                        $businessPage->setSlug($virtualBusinessPage->getSlug());

                        $entityManager->persist($businessPage);
                        $entityManager->flush();
                    }
                }
            }
        }
    }
}
