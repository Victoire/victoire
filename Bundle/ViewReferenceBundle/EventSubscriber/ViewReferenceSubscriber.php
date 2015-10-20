<?php

namespace Victoire\Bundle\ViewReferenceBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Victoire\Bundle\BusinessPageBundle\Builder\BusinessPageBuilder;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Entity\Route;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\ViewReferenceBundle\Builder\ViewReferenceBuilder;
use Victoire\Bundle\ViewReferenceBundle\Cache\Xml\ViewReferenceXmlCacheManager;
use Victoire\Bundle\ViewReferenceBundle\Provider\ViewReferenceProvider;

/**
 * Tracks if a slug changed and re-compute the view cache
 * ref: victoire_view_reference.event_subscriber.
 */
class ViewReferenceSubscriber implements EventSubscriber
{
    protected $urlBuilder;
    protected $viewCacheManager;
    protected $businessPageBuilder;
    protected $viewReferenceProvider;

    /**
     * @param UrlBuilder           $urlBuilder
     * @param ViewReferenceXmlCacheManager $viewCacheManager
     * @param BusinessPageBuilder  $businessPageBuilder
     * @param ViewReferenceBuilder $viewReferenceBuilder
     */
    public function __construct(
        UrlBuilder $urlBuilder,
        ViewReferenceXmlCacheManager $viewCacheManager,
        BusinessPageBuilder $businessPageBuilder,
        ViewReferenceBuilder $viewReferenceBuilder,
        ViewReferenceProvider $viewReferenceProvider
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->viewCacheManager = $viewCacheManager;
        $this->businessPageBuilder = $businessPageBuilder;
        $this->viewReferenceBuilder = $viewReferenceBuilder;
        $this->viewReferenceProvider = $viewReferenceProvider;
    }

    /**
     * bind to LoadClassMetadata method.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
            'postPersist',
        ];
    }

    /**
     * Will rebuild url if needed and update cache.
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $eventArgs->getEntityManager();
        /** @var UnitOfWork $uow */
        $uow = $entityManager->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof View) {
                if (((array_key_exists('slug', $uow->getEntityChangeSet($entity)) //the slug of the page has been modified
                    || array_key_exists('staticUrl', $uow->getEntityChangeSet($entity))
                    || array_key_exists('parent', $uow->getEntityChangeSet($entity))
                    || array_key_exists('template', $uow->getEntityChangeSet($entity)))
                )) {
                    $this->manageView($entity, $entityManager, $uow);
                }
            }
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof View) {
                $this->manageView($entity, $entityManager, $uow, true);
            }
        }
    }

    /**
     * When a page is inserted, compute its url and children urls.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof View) {
            $em = $eventArgs->getEntityManager();
            $this->manageView($entity, $em, $em->getUnitOfWork());
        }
    }

    /**
     * Change url recursively for the WebViewInterface given.
     * @param View $view
     *
     * @return void
     */
    protected function updateCache(View $view, EntityManager $em, UnitOfWork $uow, $delete = false)
    {
        $viewReferences = [];
        $referencableViews = $this->viewReferenceProvider->getReferencableViews($view, $em);

        foreach ($referencableViews as $referencableView) {
            $referencableReferences = $this->viewReferenceBuilder->buildViewReference($referencableView, $em);
            $viewReferences = array_merge($viewReferences, $referencableReferences);

            if ($delete) {
                $this->viewCacheManager->removeViewsReferencesByParameters($referencableReferences);
            } else {
                $this->viewCacheManager->update($referencableReferences);
            }
        }

        foreach ($viewReferences as $key => $viewReference) {
            if ($view instanceof WebViewInterface && $view->getId() && !$delete) {
                $this->addRouteHistory($viewReference['view'], $em, $uow);
            }
        }

        foreach ($view->getChildren() as $_child) {
            $this->manageView($_child, $em, $uow, $delete);
        }
    }

    /**
     * Manage urls.
     *
     * @param View $view
     *
     * @return void
     */
    protected function manageView(View $view, EntityManager $em, UnitOfWork $uow, $delete = false)
    {
        if ($view instanceof BusinessPage && !$delete) {
            $oldSlug = $view->getSlug();
            $staticUrl = $view->getStaticUrl();
            $computedPage = $this->businessPageBuilder->generateEntityPageFromTemplate($view->getTemplate(), $view->getBusinessEntity(), $em);
            $newSlug = $computedPage->getSlug();

            if ($staticUrl) {
                $staticUrl = preg_replace('/'.$oldSlug.'/', $newSlug, $staticUrl);
                $view->setStaticUrl($staticUrl);
            }
            $view->setSlug($newSlug);
            $meta = $em->getClassMetadata(get_class($view));
            $em->persist($view);
            $uow->computeChangeSet($meta, $view);
        }

        $this->updateCache($view, $em, $uow, $delete);

        if ($view instanceof BusinessTemplate) {

            // Get BusinessPages of the given BusinessTemplate
            $inheritors = $em->getRepository('Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage')->findByTemplate($view);
            foreach ($inheritors as $instance) {
                $this->manageView($instance, $em, $uow, $delete);
            }
        }
    }

    /**
     * Record the route history of the page.
     *
     * @param WebViewInterface $view
     */
    protected function addRouteHistory(WebViewInterface $view, EntityManager $em, UnitOfWork $uow)
    {
        $route = new Route();
        $route->setUrl($view->getUrl());
        $route->setView($view);

        $meta = $em->getClassMetadata(get_class($view));
        $em->persist($view);
        $uow->computeChangeSet($meta, $view);

        $meta = $em->getClassMetadata(get_class($route));
        $em->persist($route);
        $uow->computeChangeSet($meta, $route);
    }
}
