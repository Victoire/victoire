<?php

namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Victoire\Bundle\BusinessPageBundle\Builder\BusinessPageBuilder;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Builder\ViewReferenceBuilder;
use Victoire\Bundle\CoreBundle\Entity\Route;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Victoire\Bundle\CoreBundle\Provider\ViewReferenceProvider;

/**
 * Tracks if a slug changed and re-compute the view cache
 * ref: victoire_core.url_subscriber
 */
class ViewReferenceSubscriber implements EventSubscriber
{
    protected $urlBuilder;
    protected $viewCacheHelper;
    protected $businessPageBuilder;
    protected $viewReferenceProvider;

    /**
     * @param UrlBuilder $urlBuilder
     * @param ViewCacheHelper $viewCacheHelper
     * @param BusinessPageBuilder $businessPageBuilder
     * @param ViewReferenceBuilder $viewReferenceBuilder
     */
    public function __construct(
        UrlBuilder $urlBuilder,
        ViewCacheHelper $viewCacheHelper,
        BusinessPageBuilder $businessPageBuilder,
        ViewReferenceBuilder $viewReferenceBuilder,
        ViewReferenceProvider $viewReferenceProvider
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->viewCacheHelper = $viewCacheHelper;
        $this->businessPageBuilder = $businessPageBuilder;
        $this->viewReferenceBuilder = $viewReferenceBuilder;
        $this->viewReferenceProvider = $viewReferenceProvider;
    }
    /**
     * bind to LoadClassMetadata method
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return array(
            'onFlush',
            'postPersist',
        );
    }

    /**
     * Will rebuild url if needed and update cache
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
                    || array_key_exists('parent', $uow->getEntityChangeSet($entity)))
                )) {
                    $this->manageViewUrl($entity, $entityManager, $uow);
                }
            }
        }
    }

    /**
     * When a page is inserted, compute its url and children urls
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof View) {
            $em = $eventArgs->getEntityManager();
            $this->manageViewUrl($entity, $em, $em->getUnitOfWork());
        }
    }

    /**
     * Change url recursively for the WebViewInterface given
     * @param WebViewInterface $view
     *
     * @return void
     */
    protected function updateCache(View $view, EntityManager $em, UnitOfWork $uow)
    {
        $viewReferences = [];
        $referencableViews = $this->viewReferenceProvider->getReferencableViews($view, $em);

        foreach ($referencableViews as $referencableView) {

            $viewReferences = $this->viewReferenceBuilder->buildViewReference($referencableView, $em);
            $viewReferences = $this->viewCacheHelper->update($viewReferences);
        }



        foreach ($viewReferences as $key => $viewReference) {
            if ($view instanceof WebViewInterface && $view->getId() ) {
                $this->addRouteHistory($viewReference['view'], $em, $uow);
            }
        }

        foreach ($view->getChildren() as $_child) {
            $this->manageViewUrl($_child, $em, $uow);
        }

    }

    /**
     * Manage urls
     * @param WebViewInterface $view
     *
     * @return void
     */
    protected function manageViewUrl(View $view, EntityManager $em, UnitOfWork $uow)
    {
        if ($view instanceof BusinessPage) {
            $oldSlug = $view->getSlug();
            $staticUrl = $view->getStaticUrl();
            $computedPage = $this->businessPageBuilder->generateEntityPageFromPattern($view->getTemplate(), $view->getBusinessEntity(), $em);
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

        $this->updateCache($view, $em, $uow);

        if ($view instanceof BusinessTemplate) {

            // Get BusinessPages of the given BusinessTemplate
            $inheritors = $em->getRepository('Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage')->findByTemplate($view);
            foreach($inheritors as $instance) {
                $this->manageViewUrl($instance, $em, $uow);
            }
        }
    }

    /**
     * Record the route history of the page
     *
     * @param WebViewInterface $view
     * @param string           $initialUrl
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
