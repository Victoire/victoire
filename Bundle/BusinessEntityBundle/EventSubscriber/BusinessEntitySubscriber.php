<?php

namespace Victoire\Bundle\BusinessEntityBundle\EventSubscriber;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\DependencyInjection\Container;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
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

    /**
     * @param BusinessPageBuilder $businessPageBuilder
     * @param BusinessEntityHelper $businessEntityHelper
     * @param BusinessPageHelper $businessPageHelper
     */
    public function __construct(BusinessPageBuilder          $businessPageBuilder,
                                BusinessEntityHelper         $businessEntityHelper,
                                BusinessPageHelper           $businessPageHelper
    ) {
        $this->businessPageBuilder = $businessPageBuilder;
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
            'postUpdate'
        ];
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $eventArgs->getEntityManager();
        /** @var UnitOfWork $uow */
        $uow = $entityManager->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);
            if ($businessEntity) {
                $this->updateBusinessPages(
                    $entity,
                    $businessEntity,
                    $entityManager,
                    $uow->getScheduledEntityDeletions()
                );
            }
        }
    }

    /**
     *
     * get BusinessTemplate concerned by this entity (if so)
     * then get BusinessPages
     * for each BusinessPage, update its slug according to the new slug (if so)
     *
     * @param $entity
     * @param BusinessEntity $businessEntity
     * @param EntityManager  $entityManager
     * @param array          $deletions
     * @throws \Exception
     * @internal param LifecycleEventArgs $eventArgs
     */
    public function updateBusinessPages($entity, BusinessEntity $businessEntity, EntityManager $entityManager, $deletions)
    {
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
                foreach ($deletions as $deletion) {
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
