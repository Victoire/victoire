<?php

namespace Victoire\Bundle\PageBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\CoreBundle\Entity\Route;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Helper\UrlHelper;

/**
 * This class listen Page Entity changes.
 */
class PageSubscriber implements EventSubscriber
{
    protected $router;
    protected $userClass;
    protected $userCallable;
    protected $viewCacheHelper;
    protected $container; // container is given here because of "em" circular reference of UrlHelper

    /**
     * Constructor
     * @param unknown         $router          @router
     * @param unknown         $userCallable    @victoire_page.user_callable
     * @param string          $userClass       %victoire_core.user_class%
     * @param ViewCacheHelper $viewCacheHelper @victoire_core.view_cache_helper
     * @param Container       $container       @service_container
     */
    public function __construct($router, $userCallable, $userClass, $viewCacheHelper, $container)
    {
        $this->router          = $router;
        $this->userClass       = $userClass;
        $this->userCallable    = $userCallable;
        $this->viewCacheHelper = $viewCacheHelper;
        $this->container       = $container;
    }

    /**
     * bind to LoadClassMetadata method
     *
     * @return array The subscribed events
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

        $metadatas = $eventArgs->getClassMetadata();

        //set a relation between Page and User to define the page author
        $metaBuilder = new ClassMetadataBuilder($metadatas);

        //Add author relation on view
        if ($this->userClass && $metadatas->name === 'Victoire\Bundle\CoreBundle\Entity\View') {
            $metaBuilder->addManyToOne('author', $this->userClass);
        }

        // if $pages property exists, add the inversed side on User
        if ($metadatas->name === $this->userClass && property_exists($this->userClass, 'pages')) {
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
        $this->entityManager = $eventArgs->getEntityManager();
        $this->uow  = $this->entityManager->getUnitOfWork();

        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof BasePage) {
                $computeUrl = ((array_key_exists('slug', $this->uow->getEntityChangeSet($entity)) //the slug of the page has been modified
                            || array_key_exists('parent', $this->uow->getEntityChangeSet($entity)))
                            && !$entity instanceof BusinessEntityPage // The url of a BusinessEntityPage has already been generated
                            ); //the parent has been modified
                if ($computeUrl) {
                    $this->buildUrl($entity);
                }
                $meta = $this->entityManager->getClassMetadata(get_class($entity));
                $this->uow->recomputeSingleEntityChangeSet($meta, $entity);
                $entity->setAuthor($this->userCallable->getCurrentUser());
            }
        }

        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof BasePage) {
                $computeUrl = ((array_key_exists('slug', $this->uow->getEntityChangeSet($entity)) //the slug of the page has been modified
                            || array_key_exists('parent', $this->uow->getEntityChangeSet($entity)))
                            && !$entity instanceof BusinessEntityPage // The url of a BusinessEntityPage has already been generated
                            ); //the parent has been modified
                if ($computeUrl) {
                    $this->buildUrl($entity);
                    $meta = $this->entityManager->getClassMetadata(get_class($entity));
                    $this->uow->computeChangeSet($meta, $entity);
                }
            }
        }
    }

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof BasePage) {
            $this->updateCache($entity);
        }
    }

    /**
     * There is changes in the Page, we have to update the page references cache file
     * @param BasePage $page the page
     *
     * @return void
     */
    protected function updateCache(BasePage $page)
    {

        if ($page instanceof BusinessEntityPagePattern) {
            foreach ($entities as $entity) {
                $this->viewCacheHelper->update($page, $entity, $viewsReference);
            }
        } else {
            $this->viewCacheHelper->update($page, null);
        }
    }
    /**
     * Builds the page's url by get all page parents slugs and implode them with "/".
     * Builds the pages children urls with new page slug
     * If page has a custom url, we don't modify it, but we modify children urls
     * @param Page $page
     * @param bool $depth
     *
     * @return $page
     */
    public function buildUrl(BasePage $page, $depth = 0)
    {
        //@todo implements BusinessEntityPagePattern urls
        if ($page instanceof BusinessEntityPagePattern) {
            return false;
        }

        //Get Initial url to historize it
        $initialUrl = $page->getUrl();

        // build url binded with parents url
        if ($page instanceof Page && $page->isHomepage()) {
            $url = array('');
        } else {
            $url = array($page->getSlug());
        }

        //get the slug of the parents
        $url = $this->getParentSlugs($page, $url);

        //reorder the list of slugs
        $url = array_reverse($url);
        //build an url based on the slugs
        $url = implode('/', $url);

        //get the next free url
        $url = $this->container->get('victoire_page.url_helper')->getNextAvailaibleUrl($url);

        //update url of the page
        $page->setUrl($url);

        //the metadata of the page
        $meta = $this->entityManager->getClassMetadata(get_class($page));

        if ($depth === 0) {
            $this->uow->recomputeSingleEntityChangeSet($meta, $page);
        } else {
            $this->uow->computeChangeSet($meta, $page);
        }

        $this->rebuildChildrenUrl($page, $depth);
        $this->addRouteHistory($page, $initialUrl);
    }

    /**
     * Get the array of slugs of the parents
     * @param Page  $page
     * @param array $slugs
     *
     * @return array $urlArray The list of slugs
     */
    protected function getParentSlugs(BasePage $page, $slugs)
    {
        $parent = $page->getParent();

        if ($parent !== null) {
            array_push($slugs, $parent->getSlug());
            if ($parent->getParent() !== null) {
                $slugs = array_merge($slugs, $this->getParentSlugs($parent, $slugs));
            }
        }

        return array_unique($slugs);
    }

    /**
     * Record the route history of the page
     *
     * @param Page   $page
     * @param String $initialUrl
     */
    protected function addRouteHistory(BasePage $page, $initialUrl)
    {
        //services
        $em = $this->entityManager;
        $uow = $this->uow;

        //if we edit page, there is an id
        if ($page->getId()) {
            $route = new Route();
            $route->setUrl($initialUrl);
            $route->setPage($page);
            $meta = $em->getClassMetadata(get_class($route));
            $em->persist($route);
            $uow->computeChangeSet($meta, $route);

            //add the route to the page
            $page->addRoute($route);
        }
    }

    /**
     * Rebuild the url for all the children
     *
     * @param Page    $page  The page
     * @param Integer $depth The depth
     */
    protected function rebuildChildrenUrl(BasePage $page, $depth)
    {
        //services
        $em = $this->entityManager;
        $uow = $this->uow;

        $children = $page->getChildren();

        //if there are some children
        if ($children) {
            //we parse the children
            foreach ($children as $child) {
                $depth++;
                // recursive call for each children
                $this->buildUrl($child, $depth);
                $meta = $em->getClassMetadata(get_class($child));
                $uow->computeChangeSet($meta, $child);
            }
        }
    }
}
