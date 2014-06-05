<?php

namespace Victoire\Bundle\PageBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Victoire\Bundle\CoreBundle\Entity\Route;
use Victoire\Bundle\PageBundle\Entity\Template;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * This class listen Page Entity changes.
 */
class PageSubscriber implements EventSubscriber
{

    protected $cacheRouteRegisterer;
    protected $router;
    protected $userClass;
    protected $userCallable;

    public function __construct($cacheRouteRegisterer, $router, $userCallable, $userClass)
    {
        $this->cacheRouteRegisterer = $cacheRouteRegisterer;
        $this->router = $router;
        $this->userClass = $userClass;
        $this->userCallable = $userCallable;
    }

    /**
     * bind to LoadClassMetadata method
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
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {

        $metadatas = $eventArgs->getClassMetadata();
        if ($metadatas->name == 'Victoire\Bundle\PageBundle\Entity\BasePage') {
            $metadatas->discriminatorMap[Page::TYPE] = 'Victoire\Bundle\PageBundle\Entity\Page';
            $metadatas->discriminatorMap[Template::TYPE] = 'Victoire\Bundle\PageBundle\Entity\Template';
        }

        //set a relation between Page and User to define the page author
        $metaBuilder = new ClassMetadataBuilder($metadatas);
        if ($this->userClass && $metadatas->name == 'Victoire\Bundle\PageBundle\Entity\BasePage') {
            $metaBuilder->addManyToOne('author', $this->userClass, 'pages');
        }

        // if $pages property exists, add the inversed side on User
        if ($metadatas->name == $this->userClass && property_exists($this->userClass, 'pages')) {
            $metaBuilder->addOneToMany('pages', 'Victoire\Bundle\PageBundle\Entity\BasePage', 'author');
        }
    }

    /**
     * This method is called on flush
     *
    * @param OnFlushEventArgs $eventArgs The flush event args.
    * @return void
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->entityManager = $eventArgs->getEntityManager();
        $this->uow  = $this->entityManager->getUnitOfWork();
        $eventManager = $this->entityManager->getEventManager();

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
    *
    * @param Page $page
    * @param bool $depth
    * @return $page
     */
    public function buildUrl($page, $depth = 0)
    {
        //if slug changed or child page
        if (array_key_exists('slug', $this->uow->getEntityChangeSet($page)) || $depth !== 0) {
            //Get Initial url to historize it
            $initialUrl = $page->getUrl();

            // build url binded with parents url
            if ($page->isHomepage()) {
                $url = array('');
            } else {
                if ($page->getUrl() !== null && $page->getUrl() !== '') {
                    $url = array($page->getUrl());
                } else {
                    $url = array($page->getSlug());
                }
            }

            $_page = $page;

            while ($_page = $_page->getParent()) {
                if (!$_page->isHomepage()) {
                    array_push($url, $_page->getSlug());
                }
            }

            $url = array_reverse($url);
            $url = implode('/', $url);
            $page->setUrl($url);

            //if we edit page
            if ($page->getId()) {
                if ($depth === 0) {

                    $route = new Route();
                    $route->setUrl($initialUrl);
                    $route->setPage($page);
                    $meta = $this->entityManager->getClassMetadata(get_class($route));
                    $this->entityManager->persist($route);
                    $this->uow->computeChangeSet($meta, $route);
                    $page->addRoute($route);
                }
            }

            if ($depth === 0) {
                $meta = $this->entityManager->getClassMetadata(get_class($page));
                $this->uow->recomputeSingleEntityChangeSet($meta, $page);
            } else {
                $meta = $this->entityManager->getClassMetadata(get_class($page));
                $this->uow->computeChangeSet($meta, $page);
            }

            if ($page->getChildren()) {
                foreach ($page->getChildren() as $child) {
                    $depth++;
                    // recursive call for each children
                    $this->buildUrl($child, $depth);
                    $meta = $this->entityManager->getClassMetadata(get_class($child));
                    $this->uow->computeChangeSet($meta, $child);
                }
            }
        }
    }
}
