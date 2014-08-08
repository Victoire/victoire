<?php

namespace Victoire\Bundle\PageBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
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
    protected $container;

    /**
     * Constructor
     * @param unknown   $router
     * @param unknown   $userCallable
     * @param string    $userClass
     * @param Container $container
     */
    public function __construct($router, $userCallable, $userClass, $container)
    {
        $this->router = $router;
        $this->userClass = $userClass;
        $this->userCallable = $userCallable;
        $this->container = $container;
    }

    /**
     * Get the url helper
     *
     * @return UrlHelper
     */
    public function getUrlHelper()
    {
        $urlHelper = $this->container->get('victoire_page.url_helper');

        return $urlHelper;
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
                if ($entity->getComputeUrl()) {
                    $this->buildUrl($entity);
                    $meta = $this->entityManager->getClassMetadata(get_class($entity));
                    $this->uow->recomputeSingleEntityChangeSet($meta, $entity);
                    $entity->setAuthor($this->userCallable->getCurrentUser());
                }
            }
        }

        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof BasePage) {
                if ($entity->getComputeUrl()) {
                    $meta = $this->entityManager->getClassMetadata(get_class($entity));
                    $this->uow->computeChangeSet($meta, $entity);
                    $this->buildUrl($entity);
                }
            }
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
        //services
        $em = $this->entityManager;
        $uow = $this->uow;
        $urlHelper = $this->getUrlHelper();

        //if slug changed or child page
        $buildUrl = false;

        //the slug of the page has been modified
        if (array_key_exists('slug', $uow->getEntityChangeSet($page))) {
            $buildUrl = true;
        }

        //the depth is > 0, so this page is a child
        if ($depth !== 0) {
             $buildUrl = true;
        }

        //@todo wtf ?
        if ($page instanceof BusinessEntityPagePattern) {
            $buildUrl = false;
        }

        //should we build the url
        if ($buildUrl) {
            //Get Initial url to historize it
            $initialUrl = $page->getUrl();

            // build url binded with parents url
            if ($page->isHomepage()) {
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
            $url = $urlHelper->getNextAvailaibleUrl($url);

            //update url of the page
            $page->setUrl($url);

            //the metadata of the page
            $meta = $em->getClassMetadata(get_class($page));

            if ($depth === 0) {
                $uow->recomputeSingleEntityChangeSet($meta, $page);
            } else {
                $uow->computeChangeSet($meta, $page);
            }

            $this->rebuildChildrenUrl($page, $depth);
            $this->addRouteHistory($page, $initialUrl);
        }
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
            if (!$parent->isHomepage()) {
                array_push($slugs, $parent->getSlug());
            }
        }

        return $slugs;
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
