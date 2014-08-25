<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Doctrine\Orm\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\BusinessEntityPageBundle\Helper\BusinessEntityPageHelper;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;
use Victoire\Bundle\CoreBundle\Template\TemplateMapper;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Matcher\UrlMatcher;
use Victoire\Bundle\SeoBundle\Helper\PageSeoHelper;

/**
 * Page helper
 * ref: victoire_page.page_helper
 */
class PageHelper
{
    protected $parameterConverter = null;
    protected $businessEntityHelper = null;
    protected $em; // @doctrine.orm.entity_manager'
    protected $urlHelper; // @victoire_page.url_helper'
    protected $urlMatcher; // @victoire_page.matcher.url_matcher'
    protected $currentViewHelper; // @victoire_core.current_view
    protected $eventDispatcher; // @event_dispatcher
    protected $victoireTemplating; // @victoire_templating
    protected $pageSeoHelper; // @victoire_seo.helper.pageseo_helper
    protected $pageCacheHelper; // @victoire_page.page_cache_helper
    protected $session; // @session
    protected $securityContex; // @security.context


    //@todo Make it dynamic please
    protected $pageParameters = array(
        'name',
        'bodyId',
        'bodyClass',
        'slug',
        'url'
    );

    /**
     * Constructor
     * @param ParameterConverter       $parameterConverter
     * @param BusinessEntityHelper     $businessEntityHelper
     * @param BusinessEntityPageHelper $businessEntityPageHelper
     * @param EntityManager            $em
     * @param UrlHelper                $urlHelper
     * @param UrlMatcher               $urlMatcher
     * @param CurrentViewHelper        $currentViewHelper
     * @param EventDispatcherInterface $eventDispatcher
     * @param VictoireTemplating       $victoireTemplating
     * @param PageSeoHelper            $pageSeoHelper
     * @param PageCacheHelper          $pageCacheHelper
     * @param Session                  $session
     * @param SecurityContext          $securityContext
     */
    public function __construct(
        ParameterConverter $parameterConverter,
        BusinessEntityHelper $businessEntityHelper,
        BusinessEntityPageHelper $businessEntityPageHelper,
        EntityManager $em,
        UrlHelper $urlHelper,
        UrlMatcher $urlMatcher,
        CurrentViewHelper $currentViewHelper,
        EventDispatcherInterface $eventDispatcher,
        TemplateMapper $victoireTemplating,
        PageSeoHelper $pageSeoHelper,
        PageCacheHelper $pageCacheHelper,
        Session $session,
        SecurityContext $securityContext
    )
    {
        $this->parameterConverter = $parameterConverter;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->businessEntityPageHelper = $businessEntityPageHelper;
        $this->em = $em;
        $this->urlHelper = $urlHelper;
        $this->urlMatcher = $urlMatcher;
        $this->currentViewHelper = $currentViewHelper;
        $this->eventDispatcher = $eventDispatcher;
        $this->victoireTemplating = $victoireTemplating;
        $this->pageSeoHelper = $pageSeoHelper;
        $this->pageCacheHelper = $pageCacheHelper;
        $this->session = $session;
        $this->securityContext = $securityContext;

    }

    /**
     * generates a response from a page url
     * @param string $url
     *
     * @return Response
     */
    public function renderPageByUrl($url)
    {
        $page = $this->findPageByUrl($url);
        $entity = $this->findEntityByPageUrl($url);
        if ($entity) {
            $this->isPageValid($page, $entity);
            $page = $this->updatePageWithEntity($page, $entity);
        }

        //Define current view
        $this->currentViewHelper->setCurrentView($page);

        $event = new \Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent($page, $entity);

        $eventName = 'victoire_core.' . $page->getType() . '_menu.contextual';
        $this->eventDispatcher->dispatch($eventName, $event);

        $layout = 'AppBundle:Layout:' . $page->getTemplate()->getLayout() . '.html.twig';

        //create the response
        $response = $this->victoireTemplating->renderResponse($layout, array(
            "view" => $page
        ));

        return $response;

    }
    /**
     * populate the page with given entity
     * @param View           $page
     * @param BusinessEntity $entity
     *
     * @return BusinessEntityPage
     */
    public function updatePageWithEntity(View $page, $entity)
    {
        $page = $this->businessEntityPageHelper->generateEntityPageFromPattern($page, $entity);
        $this->pageSeoHelper->updateSeoByEntity($page, $entity);

        //update the parameters of the page
        $this->updatePageParametersByEntity($page, $entity);

        return $page;
    }

    /**
     * read the cache to find entity according tu given url
     * @param string $url
     *
     * @return BusinessEntity|null
     */
    public function findEntityByPageUrl($url)
    {
        $pageParameters = $this->pageCacheHelper->getPageParameters($url);

        $entity = null;
        if (!empty($pageParameters['entityId'])) {
            $entity = $this->em->getRepository($pageParameters['entityNamespace'])->findOneById($pageParameters['entityId']);
        }

        return $entity;

    }

    /**
     * Search a page in the route history according to giver url
     * @param string $url
     *
     * @return BasePage|null
     */
    public function findPageInRouteHistory($url)
    {
        $route = $this->em->getRepository('VictoireCoreBundle:Route')->findOneMostRecentByUrl($url);
        if ($route !== null) {
            //the page linked to the old url
            return $route->getPage();
        }

        return null;
    }

    /**
     * find the page according to given url. If not found, try in route history, if seo redirect, return target
     * @param string $url
     *
     * @return View
     */
    public function findPageByUrl($url)
    {
        $pageParameters = $this->pageCacheHelper->getPageParameters($url);
        $page = null;
        //get the page
        if (!empty($pageParameters['pageId'])) {
            $page = $this->em->getRepository('VictoirePageBundle:BasePage')->findOneById($pageParameters['pageId']);
        }

        if (!$page) {
            $page = $this->findPageInRouteHistory($url);
        }

        if ($page
            && $page->getSeo()
            && $page->getSeo()->getRedirectTo()
            && !$this->session->get('victoire.edit_mode', false)) {
            $page =  $page->getSeo()->getRedirectTo();
        }

        return $page;
    }


    /**
     * If the valid is not valid, an exception is thrown
     * @param Page   $page
     * @param Entity $entity
     *
     * @throws NotFoundHttpException
     * @todo  REFACTOR
     */
    protected function isPageValid($page, $entity = null)
    {
        $errorMessage = 'The page was not found.';

        //there is no page
        if ($page === null) {
            throw new NotFoundHttpException($errorMessage);
        }

        $isPublished = $page->isPublished();
        $isPageOwner = $this->securityContext->isGranted('PAGE_OWNER', $page);

        //a page not published, not owned, nor granted throw an exception
        if (!$isPublished && !$isPageOwner) {
            throw new NotFoundHttpException($errorMessage);
        }

        //if the page is a BusinessEntityPagePattern and the entity is not allowed for this page pattern
        if ($page instanceof BusinessEntityPagePattern) {
            //only victoire users are able to access a business page
            if (!$this->securityContext->isGranted('ROLE_VICTOIRE')) {
                throw new AccessDeniedException('You are not allowed to see this page');
            }
        } elseif ($page instanceof BusinessEntityPage) {
            if ($entity !== null) {
                $entityAllowed = $this->businessEntityPageHelper->isEntityAllowed($page, $entity);

                if ($entityAllowed === false) {
                    throw new NotFoundHttpException('The entity ['.$entity->getId().'] is not allowed for the page pattern ['.$page->getId().']');
                }
            }
        }
    }

    /**
     * Create an instance of the business entity page
     * @param BusinessEntityPagePattern $businessEntityPagePattern The business entity page
     * @param entity                    $entity                    The entity
     * @param string                    $url                       The new url
     *
     * @return \Victoire\Bundle\PageBundle\Entity\Page
     */
    public function createPageInstanceFromBusinessEntityPagePattern(BusinessEntityPagePattern $businessEntityPagePattern, $entity, $url)
    {
        //create a new page
        $newPage = new Page();

        $parentPage = $businessEntityPagePattern->getParent();

        //set the page parameter by the business entity page
        $newPage->setParent($parentPage);
        $newPage->setTemplate($businessEntityPagePattern);
        $newPage->setUrl($url);

        $newPage->setTitle($businessEntityPagePattern->getTitle());

        //update the parameters of the page
        $this->updatePageParametersByEntity($newPage, $entity);

        $entityProxy = new EntityProxy();
        $entityProxy->setEntity($entity);

        $newPage->setEntityProxy($entityProxy);

        return $newPage;
    }

    /**
     * Generate update the page parameters with the entity
     *
     * @param BasePage $page
     * @param Entity   $entity
     */
    public function updatePageParametersByEntity(BasePage $page, $entity)
    {
        //if no entity is provided
        if ($entity === null) {
            //we look for the entity of the page
            if ($page->getBusinessEntity() !== null) {
                $entity = $page->getBusinessEntity();
            }
        }

        //only if we have an entity instance
        if ($entity !== null) {
            $className = get_class($entity);

            $businessEntity = $this->businessEntityHelper->findByClassname($className);

            if ($businessEntity !== null) {

                $businessProperties = $this->businessEntityPageHelper->getBusinessProperties($businessEntity);

                //parse the business properties
                foreach ($businessProperties as $businessProperty) {
                    //parse of seo attributes
                    foreach ($this->pageParameters as $pageAttribute) {
                        $string = $this->getEntityAttributeValue($page, $pageAttribute);
                        $updatedString = $this->parameterConverter->setBusinessPropertyInstance($string, $businessProperty, $entity);
                        $this->setEntityAttributeValue($page, $pageAttribute, $updatedString);
                    }
                }
            }
        }
    }

    /**
     * If the current page is a business entity page pattern and where are displaying an instance
     * We create a new page for this instance
     * @param Page $page The page of the widget
     *
     * @return Page The page for the entity instance
     */
    public function duplicatePagePatternIfPageInstance(View $page)
    {
        //we copy the reference to the widget page
        $widgetPage = $page;

        //services
        $em = $this->em;

        //if the url of the referer is not the same as the url of the page of the widget
        //it means we are in a business entity template page and displaying an instance
        $url = $this->urlHelper->getAjaxUrlRefererWithoutBase();
        $widgetPageUrl = $widgetPage->getUrl();

        //the widget is linked to a page url that is not the current page url
        if ($url !== $widgetPageUrl) {
            //we try to get the page if it exists
            $pageRepository = $em->getRepository('VictoirePageBundle:Page');

            //get the page
            $page = $pageRepository->findOneByUrl($url);

            //no page were found
            if ($page === null) {
                $instance = $this->urlMatcher->getBusinessEntityPageByUrl($url);

                //an instance of a business entity page pattern and an entity has been identified
                if ($instance !== null) {
                    $template = $instance['businessEntityPagePattern'];
                    $entity = $instance['entity'];
                    //so we duplicate the business entity page for this current instance
                    $page = $this->createPageInstanceFromBusinessEntityPagePattern($template, $entity, $url);

                    //the page
                    $em->persist($page);
                    $em->flush();
                } else {
                    //we restore the widget page as the page
                    //we might be editing a template
                    $page = $widgetPage;
                }
            }
        }

        return $page;
    }

    /**
     * This method get all pages in DB, including instancified patterns related to it's entity
     * @return array the computed pages as array
     */
    public function getAllPages()
    {
        $pages = array();
        //This query is not optimized because we need the property "businessEntityName" later, and it's only present in Pattern pages
        $basePages = $this->em->createQuery("select bp from VictoirePageBundle:BasePage bp")->getResult();
        $businessEntities = $this->businessEntityHelper->getBusinessEntities();

        foreach ($basePages as $page) {
            // if page is a pattern, compute it's bep
            if ($page instanceof BusinessEntityPagePattern) {

                foreach ($businessEntities as $businessEntity) {
                    $properties = $this->businessEntityPageHelper->getBusinessProperties($businessEntity);

                    //find businessEdietifiers of the current businessEntity
                    $selectableProperties = array('id');
                    foreach ($properties as $property) {
                        if ($property->getType() === 'businessIdentifier') {
                            $selectableProperties[] = $property->getEntityProperty();
                        }
                    }
                    // This query retrieve business entity object, without useless properties for performance optimisation
                    $entities = $this->em->createQuery("select partial
                        e.{" . implode(', ', $selectableProperties) . "}
                        from ". $businessEntity->getClass() ." e")
                        ->getResult();
                    // for each business entity
                    foreach ($entities as $entity) {
                        //and for each page

                        // only if related pattern entity is the current entity
                        if ($page->getBusinessEntityName() === $businessEntity->getId()) {
                            $currentPattern = clone $page;
                            $this->updatePageParametersByEntity($currentPattern, $entity);
                            $pages['victoire_page_' . $currentPattern->getId() . '_' . $entity->getId()] = array(
                                'url' => $currentPattern->getUrl(),
                                'pageId' => $currentPattern->getId(),
                                'entityId' => $entity->getId(),
                                'entityNamespace' => get_class($entity)
                            );
                        }
                    }
                }
            } else {
                $pages['victoire_page_' . $page->getId()] = array(
                        'url' => $page->getUrl(),
                        'pageId' => $page->getId(),
                        'entityId' => null,
                        'entityNamespace' => null
                    );
            }
        }

        return $pages;
    }

    /**
     * Get the content of an attribute of an entity given
     *
     * @param entity $entity
     * @param strin  $field
     *
     * @return mixed
     */
    protected function getEntityAttributeValue($entity, $field)
    {
        $functionName = 'get'.ucfirst($field);

        $fieldValue = call_user_func(array($entity, $functionName));

        return $fieldValue;
    }

    /**
     * Update the value of the entity
     * @param Object $entity
     * @param string $field
     * @param string $value
     *
     * @return mixed
     */
    protected function setEntityAttributeValue($entity, $field, $value)
    {
        $functionName = 'set'.ucfirst($field);

        call_user_func(array($entity, $functionName), $value);
    }
}
