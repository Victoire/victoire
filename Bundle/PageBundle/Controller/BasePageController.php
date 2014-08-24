<?php
namespace Victoire\Bundle\PageBundle\Controller;

use AppVentus\Awesome\ShortcutsBundle\Controller\AwesomeController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Helper\UrlHelper;

/**
 * The base page controller is used to interact with all kind of pages
 **/
class BasePageController extends AwesomeController
{

    public function showAction($url)
    {
        $entity = $page = $pageId = $entityId = null;

        $pagesCache = $this->container->get('victoire_page.page_cache_helper')->readCache();
        $pageCache = $pagesCache->xpath("//page[@url='" . $url . "']");
        if ($pageCache) {
            $pageId = $pageCache[0]->getAttributeAsPhp('view');
            $entityId = $pageCache[0]->getAttributeAsPhp('entity');
        }

        //manager
        $em = $this->getEntityManager();

        //get the page
        $page = $em->getRepository('VictoirePageBundle:BasePage')->findOneById($pageId);

        if (!$page) {
            $route = $em->getRepository('VictoireCoreBundle:Route')->findOneMostRecentByUrl($url);
            if ($route !== null) {
                //the page linked to the old url
                $page = $route->getPage();
                $router = $this->container->get('router');

                return $this->redirect($this->generateUrl('victoire_core_page_show', array('url' => $page->getUrl())));
            } else {
                $this->isPageValid($page, $entity);
            }
        }

        if ($page->getSeo()
            && $page->getSeo()->getRedirectTo()
            && !$this->get('session')->get('victoire.edit_mode', false)) {
            //a redirection is wanted by the seo bundle
            $seoUrl = $page->getSeo()->getRedirectTo()->getUrl();

            //generate the url
            return $this->redirect($this->generateUrl('victoire_core_page_show', array('url' => $seoUrl)));
        }

        if ($entityId) {
            $entityNamespace = $route->getAttributeAsPhp('entityNamespace');
            $entity = $em->getRepository($entityNamespace)->findOneById($entityId);
            $page = $this->get('victoire_business_entity_page.business_entity_page_helper')->generateEntityPageFromPattern($page, $entity);

            $this->get('victoire_seo.helper.pageseo_helper')->updateSeoByEntity($page, $entity);

            //update the parameters of the page
            $this->get('victoire_page.page_helper')->updatePageParametersByEntity($page, $entity);
        }

        //Define current view
        $this->container->get('victoire_core.current_view')->setCurrentView($page);

        $event = new \Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent($page, $entity);

        $eventName = 'victoire_core.' . $page->getType() . '_menu.contextual';
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        //the victoire templating
        $victoireTemplating = $this->container->get('victoire_templating');
        $layout = 'AppBundle:Layout:' . $page->getTemplate()->getLayout() . '.html.twig';

        $this->container->get('victoire_core.current_view')->setCurrentView($page);

        //create the response
        $response = $victoireTemplating->renderResponse($layout, array(
            "view" => $page
        ));

        //throw an exception is the page is not valid
        $this->isPageValid($page, $entity);

        return $response;
    }
    /**
     * New page
     * @param boolean $isHomepage
     *
     * @return template
     */
    protected function newAction($isHomepage = false)
    {
        $em = $this->getEntityManager();
        $page = $this->getNewPage();
        $page->setHomepage($isHomepage ? $isHomepage : 0);

        $form = $this->container->get('form.factory')->create($this->getNewPageType(), $page);

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            if ($page->getParent()) {
                $pageNb = count($page->getParent()->getChildren());
            } else {
                $pageNb = count($em->getRepository('VictoirePageBundle:BasePage')->findByParent(null));
            }
            // + 1 because position start at 1, not 0
            $page->setPosition($pageNb + 1);

            $page->setAuthor($this->getUser());
            $em->persist($page);
            $em->flush();

            return array(
                "success"  => true,
                "url"      => $this->generateUrl('victoire_core_page_show', array('url' => $page->getUrl()))
            );
        } else {
            $formErrorService = $this->container->get('av.form_error_service');

            return array(
                "success" => false,
                "message" => $formErrorService->getRecursiveReadableErrors($form),
                'html'    => $this->container->get('victoire_templating')->render(
                    $this->getBaseTemplatePath() . ':new.html.twig',
                    array('form' => $form->createView())
                )
            );
        }
    }

    /**
     * Page settings
     *
     * @param Request  $request
     * @param BasePage $page
     *
     * @return template
     */
    protected function settingsAction(Request $request, BasePage $page)
    {
        $em = $this->getEntityManager();

        $response = array();

        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->create($this->getPageSettingsType(), $page);

        //services
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');

        $businessProperties = array();

        //if the page is a business entity page
        if ($page instanceof BusinessEntityPagePattern) {
            //get the id of the business entity
            $businessEntityId = $page->getBusinessEntityName();
            //we can use the business entity properties on the seo
            $businessEntity = $businessEntityHelper->findById($businessEntityId);

            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        //the type of method used
        $requestMethod = $request->getMethod();

        //if the form is posted
        if ($requestMethod === 'POST') {
            //bind data to the form
            $form->handleRequest($this->get('request'));

            //the form should be valid
            if ($form->isValid()) {
                $em->persist($page);
                $em->flush();

                $response =  array(
                    'success' => true,
                    'url'     => $this->generateUrl('victoire_core_page_show', array('url' => $page->getUrl()))
                );
            } else {
                $formErrorService = $this->get('av.form_error_service');
                $errors = $formErrorService->getRecursiveReadableErrors($form);

                $response =  array(
                    'success' => false,
                    'message' => $errors
                );
            }
        } else {
            //we display the form
            $response = array(
                'success' => false,
                'html' => $this->container->get('victoire_templating')->render(
                    $this->getBaseTemplatePath() . ':settings.html.twig',
                    array(
                        'page' => $page,
                        'form' => $form->createView(),
                        'businessProperties' => $businessProperties
                    )
                )
            );
        }

        return $response;
    }

    /**
     * @param Page $page The page to delete
     *
     * @return template
     */
    public function deleteAction(BasePage $page)
    {
        $return = null;

        try {
            //it should not be allowed to try to delete an undeletable page
            if ($page->isUndeletable()) {
                $message = $this->get('translator')->trans('page.undeletable', array(), 'victoire');
                throw new \Exception($message);
            }

            //the entity manager
            $em = $this->getEntityManager();

            //remove the page
            $em->remove($page);

            //flush the modifications
            $em->flush();

            //redirect to the homepage
            $homepageUrl = $this->generateUrl('victoire_core_page_homepage');

            $return = array(
                'success' => true,
                'url'     => $homepageUrl
            );
        } catch (\Exception $ex) {
            $return = array(
                'success' => false,
                'message' => $ex->getMessage()
            );
        }

        return $return;
    }

    /**
     * If the valid is not valid, an exception is thrown
     * @todo  REFACTOR
     * @param Page   $page
     * @param Entity $entity
     *
     * @throws NotFoundHttpException
     */
    protected function isPageValid($page, $entity)
    {
        //services
        $securityContext = $this->get('security.context');

        $errorMessage = 'The page was not found.';

        //there is no page
        if ($page === null) {
            throw new NotFoundHttpException($errorMessage);
        }

        $isPublished = $page->isPublished();
        $isPageOwner = $securityContext->isGranted('PAGE_OWNER', $page);

        //a page not published, not owned, nor granted throw an exception
        if (!$isPublished && !$isPageOwner) {
            throw new NotFoundHttpException($errorMessage);
        }

        //if the page is a BusinessEntityPagePattern and the entity is not allowed for this page pattern
        if ($page instanceof BusinessEntityPagePattern) {

            //only victoire users are able to access a business page
            if (!$securityContext->isGranted('ROLE_VICTOIRE')) {
                throw $this->createAccessDeniedException('You are not allowed to see this page');
            }
        } elseif ($page instanceof BusinessEntityPage) {
            if ($entity !== null) {
                $businessEntityPagePatternHelper = $this->get('victoire_business_entity_page.business_entity_page_helper');
                $entityAllowed = $businessEntityPagePatternHelper->isEntityAllowed($page, $entity);

                if ($entityAllowed === false) {
                    throw $this->createNotFoundException('The entity ['.$entity->getId().'] is not allowed for the page pattern ['.$page->getId().']');
                }
            }
        }
    }

    /**
     * Get the url helper
     *
     * @return UrlHelper
     */
    public function getUrlHelper()
    {
        $helper = $this->get('victoire_page.url_helper');

        return $helper;
    }
}
