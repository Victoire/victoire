<?php

namespace Victoire\Bundle\PageBundle\Helper;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Orm\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Builder\BusinessPageBuilder;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\CoreBundle\Entity\Link;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Event\PageRenderEvent;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\SeoBundle\Helper\PageSeoHelper;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;
use Victoire\Bundle\ViewReferenceBundle\Exception\ViewReferenceNotFoundException;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\BusinessPageReference;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;
use Victoire\Bundle\WidgetMapBundle\Warmer\WidgetDataWarmer;

/**
 * Page helper
 * ref: victoire_page.page_helper.
 */
class PageHelper
{
    protected $businessEntityHelper;
    protected $entityManager;
    protected $viewReferenceHelper;
    protected $currentViewHelper;
    protected $eventDispatcher;
    protected $container;
    protected $pageSeoHelper;
    protected $session;
    protected $tokenStorage;
    protected $widgetMapBuilder;
    protected $businessPageBuilder;
    protected $businessPageHelper;
    protected $viewReferenceRepository;
    protected $widgetDataWarmer;

    /**
     * @param BusinessEntityHelper     $businessEntityHelper
     * @param EntityManager            $entityManager
     * @param ViewReferenceHelper      $viewReferenceHelper
     * @param CurrentViewHelper        $currentViewHelper
     * @param EventDispatcherInterface $eventDispatcher
     * @param Container                $container
     * @param PageSeoHelper            $pageSeoHelper
     * @param Session                  $session
     * @param TokenStorage             $tokenStorage
     * @param AuthorizationChecker     $authorizationChecker
     * @param WidgetMapBuilder         $widgetMapBuilder
     * @param BusinessPageBuilder      $businessPageBuilder
     * @param BusinessPageHelper       $businessPageHelper
     * @param WidgetDataWarmer         $widgetDataWarmer
     * @param ViewReferenceRepository  $viewReferenceRepository
     */
    public function __construct(
        BusinessEntityHelper $businessEntityHelper,
        EntityManager $entityManager,
        ViewReferenceHelper $viewReferenceHelper,
        CurrentViewHelper $currentViewHelper,
        EventDispatcherInterface $eventDispatcher,
        Container $container,
        PageSeoHelper $pageSeoHelper,
        Session $session,
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker,
        WidgetMapBuilder $widgetMapBuilder,
        BusinessPageBuilder $businessPageBuilder,
        BusinessPageHelper $businessPageHelper,
        WidgetDataWarmer $widgetDataWarmer,
        ViewReferenceRepository $viewReferenceRepository
    ) {
        $this->businessEntityHelper = $businessEntityHelper;
        $this->entityManager = $entityManager;
        $this->viewReferenceHelper = $viewReferenceHelper;
        $this->currentViewHelper = $currentViewHelper;
        $this->eventDispatcher = $eventDispatcher;
        $this->container = $container;
        $this->pageSeoHelper = $pageSeoHelper;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->widgetMapBuilder = $widgetMapBuilder;
        $this->businessPageBuilder = $businessPageBuilder;
        $this->businessPageHelper = $businessPageHelper;
        $this->widgetDataWarmer = $widgetDataWarmer;
        $this->viewReferenceRepository = $viewReferenceRepository;
    }

    /**
     * generates a response from parameters.
     *
     * @return View
     */
    public function findPageByParameters($parameters)
    {
        if (!empty($parameters['id']) && !preg_match('/^ref_/', $parameters['id'])) {
            $page = $this->entityManager->getRepository('VictoireCoreBundle:View')->findOneBy([
                'id' => $parameters['id'],
            ]);

            $entity = null;
            if (method_exists($page, 'getBusinessEntity')) {
                $entity = $page->getBusinessEntity();
            }
            $this->checkPageValidity($page, $entity, $parameters);
        } else {
            if (isset($parameters['id']) && isset($parameters['locale'])) {
                //if locale is missing, we add append locale
                if (preg_match('/^ref_[0-9]*$/', $parameters['id'])) {
                    $parameters['id'] .= '_'.$parameters['locale'];
                }
            }
            $viewReference = $this->viewReferenceRepository->getOneReferenceByParameters($parameters);
            if ($viewReference === null && !empty($parameters['viewId'])) {
                $parameters['templateId'] = $parameters['viewId'];
                unset($parameters['viewId']);
                $viewReference = $this->viewReferenceRepository->getOneReferenceByParameters($parameters);
            }

            if ($viewReference instanceof ViewReference) {
                $page = $this->findPageByReference($viewReference, $this->findEntityByReference($viewReference));
            } else {
                throw new ViewReferenceNotFoundException($parameters);
            }
            $page->setReference($viewReference, $viewReference->getLocale());
        }

        return $page;
    }

    /**
     * generates a response from a page url.
     * if seo redirect, return target.
     *
     * @param string $url
     *
     * @return Response
     */
    public function renderPageByUrl($url, $locale, $isAjax = false)
    {
        $page = null;
        if ($viewReference = $this->viewReferenceRepository->getReferenceByUrl($url, $locale)) {
            $page = $this->findPageByReference($viewReference, $entity = $this->findEntityByReference($viewReference));
            $this->checkPageValidity($page, $entity, ['url' => $url, 'locale' => $locale]);
            $page->setReference($viewReference);

            if ($page instanceof BasePage
                && $page->getSeo()
                && $page->getSeo()->getRedirectTo()
                && $page->getSeo()->getRedirectTo()->getLinkType() != Link::TYPE_NONE
                && !$this->session->get('victoire.edit_mode', false)) {
                $link = $page->getSeo()->getRedirectTo();

                return new RedirectResponse($this->container->get('victoire_widget.twig.link_extension')->victoireLinkUrl($link->getParameters()));
            }


            return $this->renderPage($page, $isAjax);
        } else {
            throw new NotFoundHttpException(sprintf('Page not found (url: "%s", locale: "%s")', $url, $locale));
        }
    }

    /**
     * generates a response from a page.
     *
     * @param View $view
     *
     * @return Response
     */
    public function renderPage($view, $isAjax = false)
    {
        $event = new \Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent($view);

        //Set currentView and dispatch victoire.on_render_page event with this currentView
        $this->currentViewHelper->setCurrentView($view);
        $pageRenderEvent = new PageRenderEvent($view);
        $this->eventDispatcher->dispatch('victoire.on_render_page', $pageRenderEvent);

        //Build WidgetMap
        $this->widgetMapBuilder->build($view, $this->entityManager, true);

        //Populate widgets with their data
        $this->widgetDataWarmer->warm($this->entityManager, $view);

        //Dispatch contextual event regarding page type
        if (in_array($view->getType(), ['business_page', 'virtual_business_page'])) {
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

        //Create the response
        $response = $this->container->get('templating')->renderResponse('VictoireCoreBundle:Layout:'.$layout, [
            'view' => $view,
        ]);

        return $response;
    }

    /**
     * populate the page with given entity.
     *
     * @param View           $page
     * @param BusinessEntity $entity
     */
    public function updatePageWithEntity(BusinessTemplate $page, $entity)
    {
        $page = $this->businessPageBuilder->generateEntityPageFromTemplate($page, $entity, $this->entityManager);
        $this->pageSeoHelper->updateSeoByEntity($page, $entity);

        //update the parameters of the page
        $this->businessPageBuilder->updatePageParametersByEntity($page, $entity);

        return $page;
    }

    /**
     * @param BusinessPageReference $viewReference
     *
     * @return BusinessPage
     *                      read the cache to find entity according tu given url.
     * @return object|null
     */
    protected function findEntityByReference(ViewReference $viewReference)
    {
        if ($viewReference instanceof BusinessPageReference && !empty($viewReference->getEntityId())) {
            return $this->entityManager->getRepository($viewReference->getEntityNamespace())
                ->findOneById($viewReference->getEntityId());
        }
    }

    /**
     * find the page according to given url.
     *
     * @return View
     */
    public function findPageByReference($viewReference, $entity = null)
    {
        $page = null;
        if ($viewReference instanceof BusinessPageReference) {
            if ($viewReference->getViewId()) { //BusinessPage
                $page = $this->entityManager->getRepository('VictoireCoreBundle:View')
                    ->findOneBy([
                        'id'     => $viewReference->getViewId(),
                    ]);
                $page->setCurrentLocale($viewReference->getLocale());
            } else { //VirtualBusinessPage
                $page = $this->entityManager->getRepository('VictoireCoreBundle:View')
                    ->findOneBy([
                        'id'     => $viewReference->getTemplateId(),
                    ]);
                if ($entity) {
                    if ($page instanceof BusinessTemplate) {
                        $page = $this->updatePageWithEntity($page, $entity);
                    }
                    if ($page instanceof BusinessPage) {
                        if ($page->getSeo()) {
                            $page->getSeo()->setCurrentLocale($viewReference->getLocale());
                        }
                        $this->pageSeoHelper->updateSeoByEntity($page, $entity);
                    }
                }
            }
        } elseif ($viewReference instanceof ViewReference) {
            $page = $this->entityManager->getRepository('VictoireCoreBundle:View')
                ->findOneBy([
                    'id'     => $viewReference->getViewId(),
                ]);
            $page->setCurrentLocale($viewReference->getLocale());
        } else {
            throw new \Exception(sprintf('Oh no! Cannot find a page for this ViewReference (%s)', ClassUtils::getClass($viewReference)));
        }

        return $page;
    }

    /**
     * @param View $page
     * @param $locale
     */
    private function refreshPage($page, $locale)
    {
        if ($page && $page instanceof View) {
            try {
                $this->entityManager->refresh($page->setTranslatableLocale($locale));
            } catch (ORMInvalidArgumentException $e) {
            }
        }
    }

    /**
     * If the page is not valid, an exception is thrown.
     *
     * @param mixed $page
     * @param mixed $entity
     * @param mixed $parameters
     *
     * @throws \Exception
     */
    public function checkPageValidity($page, $entity = null, $parameters = null)
    {
        $errorMessage = 'The page was not found';
        if ($parameters) {
            $errorMessage .= ' for parameters "'.implode('", "', $parameters).'"';
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

        if (!$this->authorizationChecker->isGranted('ROLE_VICTOIRE')) {
            $roles = $this->getPageRoles($page);
            if ($roles && !$this->authorizationChecker->isGranted($roles, $entity)) {
                throw new AccessDeniedException('You are not allowed to see this page, see the access roles defined in the view or it\'s parents and templates');
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

        return $viewLayout.'.html.twig';
    }

    /**
     * Find page's ancestors (templates and parents) and flatted all their roles.
     *
     * @param View $view
     *
     * @return array
     */
    private function getPageRoles(View $view)
    {
        $insertAncestorRole = function (View $view = null) use (&$insertAncestorRole) {
            if ($view === null) {
                return;
            }
            $roles = $view->getRoles();

            if ($templateRoles = $insertAncestorRole($view->getTemplate(), $roles)) {
                $roles .= ($roles ? ',' : '').$templateRoles;
            }
            if ($parentRoles = $insertAncestorRole($view->getParent(), $roles)) {
                $roles .= ($roles ? ',' : '').$parentRoles;
            }

            return $roles;
        };

        $roles = $insertAncestorRole($view);

        if ($roles) {
            return array_unique(explode(',', $roles));
        }
    }
}
