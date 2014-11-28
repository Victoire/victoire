<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Doctrine\Orm\EntityManager;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter as BETParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\BusinessEntityPageBundle\Helper\BusinessEntityPageHelper;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Victoire\Bundle\CoreBundle\Helper\ViewHelper;
use Victoire\Bundle\CoreBundle\Template\TemplateMapper;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Matcher\UrlMatcher;
use Victoire\Bundle\SeoBundle\Helper\PageSeoHelper;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;
use Doctrine\ORM\PersistentCollection;
use Victoire\Bundle\I18nBundle\Entity\I18n;

/**
 * Page helper
 * ref: victoire_page.page_helper
 */
class PageHelper extends ViewHelper
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
    protected $viewCacheHelper; // @victoire_core.view_cache_helper
    protected $session; // @session
    protected $securityContex; // @security.context
    protected $urlizer; // @gedmo.urlizer
    protected $widgetMapBuilder; // @victoire_widget_map.builder

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
     * @param BETParameterConverter    $parameterConverter
     * @param BusinessEntityHelper     $businessEntityHelper
     * @param BusinessEntityPageHelper $businessEntityPageHelper
     * @param EntityManager            $em
     * @param UrlHelper                $urlHelper
     * @param UrlMatcher               $urlMatcher
     * @param CurrentViewHelper        $currentViewHelper
     * @param EventDispatcherInterface $eventDispatcher
     * @param VictoireTemplating       $victoireTemplating
     * @param PageSeoHelper            $pageSeoHelper
     * @param ViewCacheHelper          $viewCacheHelper
     * @param Session                  $session
     * @param SecurityContext          $securityContext
     * @param Urlizer                  $urlizer
     */
    public function __construct(
        BETParameterConverter $parameterConverter,
        BusinessEntityHelper $businessEntityHelper,
        BusinessEntityPageHelper $businessEntityPageHelper,
        EntityManager $em,
        UrlHelper $urlHelper,
        UrlMatcher $urlMatcher,
        CurrentViewHelper $currentViewHelper,
        EventDispatcherInterface $eventDispatcher,
        TemplateMapper $victoireTemplating,
        PageSeoHelper $pageSeoHelper,
        ViewCacheHelper $viewCacheHelper,
        Session $session,
        SecurityContext $securityContext,
        Urlizer $urlizer,
        WidgetMapBuilder $widgetMapBuilder
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
        $this->viewCacheHelper = $viewCacheHelper;
        $this->session = $session;
        $this->securityContext = $securityContext;
        $this->urlizer = $urlizer;
        $this->widgetMapBuilder = $widgetMapBuilder;

    }

    /**
     * generates a response from a page url
     * @param string $url
     *
     * @return Response
     */
    public function findPageByParameters($parameters)
    {
        $viewReference = $this->viewCacheHelper->getReferenceByParameters($parameters);
        if ($viewReference === null && !empty($parameters['viewId'])) {
            $parameters['patternId'] = $parameters['viewId'];
            unset($parameters['viewId']);
            $viewReference = $this->viewCacheHelper->getReferenceByParameters($parameters);
        }
        $page = $this->findPageByReference($viewReference);

        return $page;
    }

    /**
     * generates a response from a page url
     * @param string $url
     *
     * @return Response
     */
    public function renderPageByUrl($url, $locale)
    {
        $page = $this->findPageByParameters(array('url' => $url, 'locale' => $locale));

        $event = new \Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent($page);

        $eventName = 'victoire_core.' . $page->getType() . '_menu.contextual';
        $this->eventDispatcher->dispatch($eventName, $event);

        $layout = 'AppBundle:Layout:' . $page->getTemplate()->getLayout() . '.html.twig';

        $this->widgetMapBuilder->build($page);
        $this->currentViewHelper->setCurrentView($page);
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
    public function updatePageWithEntity(BusinessEntityPagePattern $page, $entity)
    {
        $page = $this->businessEntityPageHelper->generateEntityPageFromPattern($page, $entity);
        $this->pageSeoHelper->updateSeoByEntity($page, $entity);

        //update the parameters of the page
        $this->updatePageParametersByEntity($page, $entity);

        $entityProxy = new EntityProxy();
        $entityProxy->setEntity($entity);
        $page->setEntityProxy($entityProxy);

        return $page;
    }

    /**
     * read the cache to find entity according tu given url
     * @param array $viewReference
     *
     * @return BusinessEntity|null
     */
    protected function findEntityByReference($viewReference)
    {

        $entity = null;
        if (!empty($viewReference['entityId'])) {
            $entity = $this->em->getRepository($viewReference['entityNamespace'])->findOneById($viewReference['entityId']);
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
    public function findPageByReference($viewReference)
    {
        $page = null;
        //get the page
        if (!empty($viewReference['viewId'])) {
            $page = $this->em->getRepository('VictoireCoreBundle:View')->findOneById($viewReference['viewId']);
        } elseif (!empty($viewReference['patternId'])) {
            $page = $this->em->getRepository('VictoireCoreBundle:View')->findOneById($viewReference['patternId']);
        }

        if (!$page) {
            $page = $this->findPageInRouteHistory($viewReference['url']);
        }

        if ($page instanceof BasePage
            && $page->getSeo()
            && $page->getSeo()->getRedirectTo()
            && !$this->session->get('victoire.edit_mode', false)) {
            $page =  $page->getSeo()->getRedirectTo();
        }

        if ($viewReference && $page instanceof View) {
            $page->setReference($viewReference);
        }

        $entity = $this->findEntityByReference($viewReference);
        if ($entity && $page instanceof BusinessEntityPagePattern) {
            $page = $this->updatePageWithEntity($page, $entity);
        }
        $this->checkPageValidity($page, $entity);

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
    protected function checkPageValidity($page, $entity = null)
    {
        $errorMessage = 'The page was not found.';

        //there is no page
        if ($page === null) {
            throw new NotFoundHttpException($errorMessage);
        }

        $isPageOwner = $this->securityContext->isGranted('PAGE_OWNER', $page);

        //a page not published, not owned, nor granted throw an exception
        if (($page instanceof BasePage && !$page->isPublished()) && !$isPageOwner) {
            throw new NotFoundHttpException($errorMessage);
        }

        //if the page is a BusinessEntityPagePattern and the entity is not allowed for this page pattern
        if ($page instanceof BusinessEntityPagePattern) {
            //only victoire users are able to access a business page
            if (!$this->securityContext->isGranted('ROLE_VICTOIRE')) {
                throw new AccessDeniedException('You are not allowed to see this page');
            }
        } elseif ($page instanceof BusinessEntityPage) {
            if (!$entity->isVisibleOnFront() && !$this->securityContext->isGranted('ROLE_VICTOIRE')) {
                throw new NotFoundHttpException('The BusinessEntityPage for '.get_class($entity).'#'.$entity->getId().' is not visible on front.');
            }
            if (!$page->getId()) {
                $entityAllowed = $this->businessEntityPageHelper->isEntityAllowed($page->getTemplate(), $entity);

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

    public function cloneView(View $view)
    {

        $clonedView = clone $view;
        $widgetSlotsClone = $clonedView->getSlots();
        $arrayMapOfWidgetMap = array();
        $originalSlots = $view->getSlots();

        $clonedView->setId(null);
        $this->em->persist($clonedView);

        foreach ($clonedView->getWidgets() as $widgetKey => $widgetVal) {
            $clonedWidget = clone $widgetVal;
            $clonedWidget->setId(null);
            $clonedWidget->setView($clonedView);
            $arrayMapOfWidgetMap[$widgetVal->getId()] = $clonedWidget; 
            $this->em->persist($clonedWidget);
        }

        $this->em->persist($clonedView);
        $this->em->refresh($view);
        $this->em->flush();
        
        $i18n = $view->getI18n();
        $i18n->setTranslation($clonedView->getLocale(), $clonedView);
        
        foreach ($widgetSlotsClone as $widgetSlotCloneKey => $widgetSlotCloneVal) {
            $widgetMapItemClone = $widgetSlotCloneVal->getWidgetMaps();
            foreach ($originalSlots as $originalSlotKey => $originalSlotVal) {
                $originalMapItem = $originalSlotVal->getWidgetMaps();
                if ($originalMapItem->getWidgetId() === $widgetMapItemClone->getWidgetId()) {
                    $widget = $arrayMapOfWidget[$originalMapItem->getWidgetId()];
                    $widgetMapItemClone->setWidgetId($widget->getId());
                }
            }
        }
            
        $clonedView->setSlots($widgetSlotsClone);
        $this->em->persist($clonedView);
        $this->em->flush();

        return $clonedView;
        
    }
}
