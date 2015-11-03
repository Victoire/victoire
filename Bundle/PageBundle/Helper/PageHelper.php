<?php

namespace Victoire\Bundle\PageBundle\Helper;

use Doctrine\Orm\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter as BETParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Builder\BusinessPageBuilder;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Event\PageRenderEvent;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\CoreBundle\Helper\ViewHelper;
use Victoire\Bundle\ViewReferenceBundle\Builder\Chain\ViewReferenceBuilderChain;
use Victoire\Bundle\CoreBundle\Template\TemplateMapper;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\SeoBundle\Helper\PageSeoHelper;
use Victoire\Bundle\ViewReferenceBundle\Builder\ViewReferenceBuilder;
use Victoire\Bundle\ViewReferenceBundle\Cache\Xml\ViewReferenceXmlCacheRepository;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Provider\ViewReferenceProvider;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;
use Victoire\Bundle\WidgetMapBundle\Warmer\WidgetDataWarmer;

/**
 * Page helper
 * ref: victoire_page.page_helper.
 */
class PageHelper extends ViewHelper
{
    protected $businessPageBuilder;
    protected $entityManager; // @doctrine.orm.entity_manager'
    protected $currentViewHelper; // @victoire_core.current_view
    protected $eventDispatcher; // @event_dispatcher
    protected $victoireTemplating; // @victoire_templating
    protected $pageSeoHelper; // @victoire_seo.helper.pageseo_helper
    protected $session; // @session
    protected $token_storage; // @security.authorization_checker
    protected $authorizationChecker; // @security.authorization_checker
    protected $widgetMapBuilder; // @victoire_widget_map.builder
    protected $viewReferenceBuilderChain; // @victoire_view_reference.builder_chain
    protected $urlBuilder; // @victoire_core.url_builder
    protected $widgetDataWarmer;
    protected $viewCacheRepository;

    protected $pageParameters = [
        'name',
        'bodyId',
        'bodyClass',
        'slug',
        'url',
    ];

    /**
     * @param BETParameterConverter     $parameterConverter
     * @param BusinessEntityHelper      $businessEntityHelper
     * @param EntityManager             $entityManager
     * @param ViewReferenceXmlCacheRepository $viewCacheRepository
     * @param ViewReferenceBuilderChain $viewReferenceBuilder
     * @param CurrentViewHelper         $currentViewHelper
     * @param EventDispatcherInterface  $eventDispatcher
     * @param TemplateMapper            $victoireTemplating
     * @param PageSeoHelper             $pageSeoHelper
     * @param Session                   $session
     * @param TokenStorage              $tokenStorage
     * @param AuthorizationChecker      $authorization_checker
     * @param WidgetMapBuilder          $widgetMapBuilder
     * @param UrlBuilder                $urlBuilder
     * @param WidgetDataWarmer          $widgetDataWarmer
     */
    public function __construct(
        BETParameterConverter $parameterConverter,
        BusinessEntityHelper $businessEntityHelper,
        EntityManager $entityManager,
        ViewReferenceBuilder $viewReferenceBuilder,
        ViewReferenceHelper $viewReferenceHelper,
        ViewReferenceProvider $viewReferenceProvider,
        CurrentViewHelper $currentViewHelper,
        EventDispatcherInterface $eventDispatcher,
        TemplateMapper $victoireTemplating,
        PageSeoHelper $pageSeoHelper,
        Session $session,
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorization_checker,
        WidgetMapBuilder $widgetMapBuilder,
        UrlBuilder $urlBuilder,
        BusinessPageBuilder $businessPageBuilder,
        BusinessPageHelper $businessPageHelper,
        WidgetDataWarmer $widgetDataWarmer,
        ViewReferenceXmlCacheRepository $viewCacheRepository
    ) {
        parent::__construct($parameterConverter, $businessEntityHelper, $entityManager, $viewReferenceBuilder, $viewReferenceHelper, $viewReferenceProvider);
        $this->businessPageBuilder = $businessPageBuilder;
        $this->businessPageHelper = $businessPageHelper;
        $this->entityManager = $entityManager;
        $this->currentViewHelper = $currentViewHelper;
        $this->eventDispatcher = $eventDispatcher;
        $this->victoireTemplating = $victoireTemplating;
        $this->pageSeoHelper = $pageSeoHelper;
        $this->viewCacheRepository = $viewCacheRepository;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorization_checker;
        $this->widgetMapBuilder = $widgetMapBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->widgetDataWarmer = $widgetDataWarmer;
    }

    /**
     * generates a response from a page url.
     *
     * @return View
     */
    public function findPageByParameters($parameters)
    {
        if (!empty($parameters['id']) && !preg_match('/^ref_/', $parameters['id'])) {
            $page = $this->em->getRepository('VictoireCoreBundle:View')->findOneById($parameters['id']);

            $entity = null;
            if (method_exists($page, 'getBusinessEntity')) {
                $entity = $page->getBusinessEntity();
            }
            $this->checkPageValidity($page, $entity, $parameters);
        } else {
            $viewReference = $this->viewCacheRepository->getOneReferenceByParameters($parameters);
            if ($viewReference === null && !empty($parameters['viewId'])) {
                $parameters['patternId'] = $parameters['viewId'];
                unset($parameters['viewId']);
                $viewReference = $this->viewCacheRepository->getOneReferenceByParameters($parameters);
            }

            $page = $this->findPageByReference($viewReference, $parameters);
        }

        return $page;
    }

    /**
     * generates a response from a page url.
     * @param string $url
     *
     * @return Response
     */
    public function renderPageByUrl($url, $locale, $isAjax = false)
    {
        $page = $this->findPageByReference($this->viewCacheRepository->getReferenceByUrl($url, $locale), [
            'url'    => $url,
            'locale' => $locale
        ]);

        return $this->renderPage($page, $isAjax);
    }

    /**
     * generates a response from a page url.
     *
     * @param View $view
     *
     * @return Response
     */
    public function renderPage($view, $isAjax = false)
    {
        $event = new \Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent($view);

        //Build WidgetMap
        $this->widgetMapBuilder->build($view, true);

        //Populate widgets with their data
        $this->widgetDataWarmer->warm($this->em, $view);

        //Dispatch contextual event regarding page type
        if ($view->getType() == 'business_page') {
            //Dispatch also an event with the Business entity name
            $eventName = 'victoire_core.page_menu.contextual';
            if (!$view->getId()) {
                $eventName = 'victoire_core.business_template_menu.contextual';
                $event = new \Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent($view->getTemplate());
            }
            $this->eventDispatcher->dispatch($eventName, $event);
            $type = $view->getBusinessEntityId();
        } else {
            $type = $view->getType();
        }

        $eventName = 'victoire_core.'.$type.'_menu.contextual';
        $this->eventDispatcher->dispatch($eventName, $event);

        //Determine which layout to use
        $layout = $this->guessBestLayoutForView($view, $isAjax);

        //Set currentView and dispatch victoire.on_render_page event with this currentView
        $this->currentViewHelper->setCurrentView($view);
        $event = new PageRenderEvent($view);
        $this->eventDispatcher->dispatch('victoire.on_render_page', $event);

        //Create the response
        $response = $this->victoireTemplating->renderResponse($layout, [
            'view' => $view,
        ]);

        return $response;
    }

    /**
     * populate the page with given entity.
     *
     * @param View           $page
     * @param BusinessEntity $entity
     *
     * @return BusinessPage
     */
    public function updatePageWithEntity(BusinessTemplate $page, $entity)
    {
        $page = $this->businessPageBuilder->generateEntityPageFromTemplate($page, $entity, $this->entityManager);
        $this->pageSeoHelper->updateSeoByEntity($page, $entity);

        //update the parameters of the page
        $this->businessPageBuilder->updatePageParametersByEntity($page, $entity);

        $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);
        $entityProxy = new EntityProxy();
        $entityProxy->setEntity($entity, $businessEntity->getName());
        $page->setEntityProxy($entityProxy);

        return $page;
    }

    /**
     * read the cache to find entity according tu given url.
     *
     * @param array $viewReference
     *
     * @return BusinessEntity|null
     */
    protected function findEntityByReference($viewReference)
    {
        $entity = null;
        if (!empty($viewReference['entityId'])) {
            $entity = $this->entityManager->getRepository($viewReference['entityNamespace'])->findOneById($viewReference['entityId']);
        }

        return $entity;
    }

    /**
     * find the page according to given url. If not found, try in route history, if seo redirect, return target.
     *
     * @return View
     */
    public function findPageByReference($viewReference, $parameters = null)
    {
        $page = null;
        //get the page
        if (!empty($viewReference['viewId'])) {
            $page = $this->entityManager->getRepository('VictoireCoreBundle:View')->findOneById($viewReference['viewId']);
        } elseif (!empty($viewReference['patternId'])) {
            $page = $this->entityManager->getRepository('VictoireCoreBundle:View')->findOneById($viewReference['patternId']);
        }


        if ($page instanceof BasePage
            && $page->getSeo()
            && $page->getSeo()->getRedirectTo()
            && !$this->session->get('victoire.edit_mode', false)) {
            $page = $page->getSeo()->getRedirectTo();
        }

        if ($viewReference && $page instanceof View) {
            $page->setReference($viewReference);
        }

        $entity = $this->findEntityByReference($viewReference);
        if ($entity) {
            if ($page instanceof BusinessTemplate) {
                $page = $this->updatePageWithEntity($page, $entity);
            } elseif ($page instanceof BusinessPage) {
                $this->pageSeoHelper->updateSeoByEntity($page, $entity);
            }
        }

        $this->checkPageValidity($page, $entity, $parameters);

        if (!empty($viewReference['url'])) {
            $page->setUrl($viewReference['url']);
        } else {
            $page->setUrl($this->urlBuilder->buildUrl($page));
        }

        return $page;
    }

    /**
     * If the valid is not valid, an exception is thrown.
     *
     * @param Page   $page
     * @param Entity $entity
     *
     * @throws NotFoundHttpException
     */
    protected function checkPageValidity($page, $entity = null, $parameters = null)
    {
        $errorMessage = 'The page was not found';
        if ($parameters) {
            array_walk($parameters, function ($key, $parameter) {
                    return $key.': '.$parameter;
                });
            $errorMessage .= ' for parameters '.implode(', ', $parameters);
        }
        $isPageOwner = false;

        //there is no page
        if ($page === null) {
            throw new NotFoundHttpException($errorMessage);
        }

        if ($this->tokenStorage->getToken()) {
            $isPageOwner = $this->authorizationChecker->isGranted('PAGE_OWNER', $page);
        }

        //a page not published, not owned, nor granted throw an exception
        if (($page instanceof BasePage && !$page->isPublished()) && !$isPageOwner) {
            throw new NotFoundHttpException($errorMessage);
        }

        //if the page is a BusinessTemplate and the entity is not allowed for this page pattern
        if ($page instanceof BusinessTemplate) {
            //only victoire users are able to access a business page
            if (!$this->authorizationChecker->isGranted('ROLE_VICTOIRE')) {
                throw new AccessDeniedException('You are not allowed to see this page');
            }
        } elseif ($page instanceof BusinessPage) {
            if ($page->getTemplate()->isAuthorRestricted() && !$this->authorizationChecker->isGranted('BUSINESS_ENTITY_OWNER', $page->getBusinessEntity())) {
                throw new AccessDeniedException('You are not allowed to see this page');
            }

            if (!$entity->isVisibleOnFront() && !$this->authorizationChecker->isGranted('ROLE_VICTOIRE')) {
                throw new NotFoundHttpException('The BusinessPage for '.get_class($entity).'#'.$entity->getId().' is not visible on front.');
            }
            if (!$page->getId()) {
                $entityAllowed = $this->businessPageHelper->isEntityAllowed($page->getTemplate(), $entity, $this->entityManager);

                if ($entityAllowed === false) {
                    throw new NotFoundHttpException('The entity ['.$entity->getId().'] is not allowed for the page pattern ['.$page->getTemplate()->getId().']');
                }
            }
        }
    }

    /**
     * Create an instance of the business entity page.
     *
     * @param BusinessTemplate $BusinessTemplate The business entity page
     * @param entity           $entity           The entity
     * @param string           $url              The new url
     *
     * @return \Victoire\Bundle\PageBundle\Entity\Page
     */
    public function createPageInstanceFromBusinessTemplate(BusinessTemplate $BusinessTemplate, $entity, $url)
    {
        //create a new page
        $newPage = new Page();

        $parentPage = $BusinessTemplate->getParent();

        //set the page parameter by the business entity page
        $newPage->setParent($parentPage);
        $newPage->setTemplate($BusinessTemplate);
        $newPage->setUrl($url);

        $newPage->setTitle($BusinessTemplate->getTitle());

        //update the parameters of the page
        $this->businessPageBuilder->updatePageParametersByEntity($newPage, $entity);

        $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);
        $entityProxy = new EntityProxy();
        $entityProxy->setEntity($entity, $businessEntity->getName());

        $newPage->setEntityProxy($entityProxy);

        return $newPage;
    }

    /**
     * Guess which layout to use for a given View.
     *
     * @param View $view
     * @param bool $isAjax
     *
     * @return string
     */
    private function guessBestLayoutForView(View $view, $isAjax)
    {
        if ($isAjax) {
            $viewLayout = 'modal';
        } elseif (method_exists($view, 'getLayout') && $view->getLayout()) {
            $viewLayout = $view->getLayout();
        } else {
            $viewLayout = $view->getTemplate()->getLayout();
        }

        return 'AppBundle:Layout:'.$viewLayout.'.html.twig';
    }
}
