<?php
namespace Victoire\Bundle\PageBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppVentus\Awesome\ShortcutsBundle\Controller\AwesomeController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Victoire\Bundle\CoreBundle\Form\PageType;
use Victoire\Bundle\CoreBundle\Form\TemplateType;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;
use Victoire\Bundle\PageBundle\Helper\UrlHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * undocumented class
 *
 **/
class BasePageController extends AwesomeController
{
    /**
     * @param $page
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
     * @param url $url
     * @return Template
     *
     */
    public function showAction($url)
    {
        //the response
        $response = null;
        $entity = null;
        $businessEntityTemplatePage = null;

        //manager
        $manager = $this->getEntityManager();
        $urlHelper = $this->getUrlHelper();
        $basePageRepository = $manager->getRepository('VictoirePageBundle:BasePage');
        $routeRepository = $manager->getRepository('VictoireCoreBundle:Route');
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');

        //get the page
        $page = $basePageRepository->findOneByUrl($url);

        //we do not try to retrieve an entity for the business entity template page
        if ($page === null) {
            //create an url matcher based on the current url
            $urlMatcher = $urlHelper->getGeneralUrlMatcher($url);

            //if the url have been shorten
            if ($urlMatcher !== null) {
                $businessEntityTemplatePage = $basePageRepository->findOneByUrl($urlMatcher);

                //a page match the entity template generator
                if ($businessEntityTemplatePage) {
                    //we look for the entity
                    $entityId = $urlHelper->getEntityIdFromUrl($url);

                    //test the entity id
                    if ($entityId === null) {
                        throw new \Exception('The id could not be retrieved from the url.');
                    }

                    $entity = $businessEntityHelper->getEntityByPageAndId($businessEntityTemplatePage, $entityId);
                }
            }
        } else {
            $entity = $page->getEntity();
        }

        //no page were found, we try to look for an BusinessEntityTemplatePage
        if ($page === null) {
            $page = $businessEntityTemplatePage;
        }

        //no need for this variable anymore
        unset($businessEntityTemplatePage);

        //no page found using the url, we look for previous url
        if ($page === null) {
            $route = $routeRepository->findOneMostRecentByUrl($url);
            if ($route !== null) {
                //the page linked to the old url
                $page = $route->getPage();

                //the current url
                $url = $page->getUrl();

                //get the base url
                $router = $this->container->get('router');
                $context = $router->getContext();
                //the host
                $host = $context->getHost();
                //the scheme
                $scheme = $context->getScheme();

                //get the complete url
                $completeUrl = $scheme.'://'.$host.'/'.$url;

                //redirect to the current url
                $response = $this->redirect($completeUrl);
            }
        } else {
            if ($page->getSeo() && $page->getSeo()->getRedirectTo() && !$this->get('session')->get('victoire.edit_mode', false)) {
                //a redirection is required by the seo
                $seoUrl = $page->getSeo()->getRedirectTo()->getUrl();

                //generate the url
                $url = $this->generateUrl('victoire_core_page_show', array('url' => $seoUrl));
                //generate the redirect
                $response = $this->redirect($url);
            } else {
                //add the page to twig
                $this->get('twig')->addGlobal('page', $page);
                $this->get('twig')->addGlobal('entity', $entity);

                $event = new \Victoire\Bundle\CoreBundle\Event\Menu\BasePageMenuContextualEvent($page, $entity);

                $eventName = 'victoire_core.' . $page->getType() . '_menu.contextual';

                $this->get('event_dispatcher')->dispatch($eventName, $event); //TODO : il serait bon de faire des constantes pour les noms d'évents

                //the victoire templating
                $victoireTemplating = $this->container->get('victoire_templating');
                $layout = 'AppBundle:Layout:' . $page->getLayout() . '.html.twig';

                $parameters = array(
                    'page' => $page,
                    'id' => $page->getId(),
                    'entity' => $entity
                );

                //create the response
                $response = $victoireTemplating->renderResponse(
                    $layout,
                    $parameters
                );
            }
        }

        //throw an exception is the page is not valid
        $this->isPageValid($page);

        return $response;
    }


    /**
     * New page
     *
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
                $pageNb = count($em->getRepository('VictoirePageBundle:Page')->findByParent(null));
            }
            // + 1 because position start at 1, not 0
            $page->setPosition($pageNb + 1);

            $template = $page->getTemplate();

            if ($template) {
                $page->setWidgetMap($template->getWidgetMap());
            }

            $page->setAuthor($this->getUser());
            $em->persist($page);
            $em->flush();

            return array(
                "success"  => true,
                "url"      => $this->generateUrl('victoire_core_page_show', array('url' => $page->getUrl()))
            );
        }

        return array(
            "success" => false,
            'html'    => $this->container->get('victoire_templating')->render(
                $this->getBaseTemplatePath() . ':new.html.twig',
                array('form' => $form->createView())
            )
        );
    }

    /**
     * Page settings
     *
     * @param page $page
     * @return template
     */
    protected function settingsAction(Request $request, BasePage $page)
    {
        $em = $this->getEntityManager();

        $response = array();

        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->create($this->getPageSettingsType(), $page);

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
                        'form' => $form->createView()
                    )
                )
            );
        }

        return $response;
    }

    /**
     * If the valid is not valid, an exception is thrown
     *
     * @param Page $page
     *
     * @throws NotFoundHttpException
     */
    protected function isPageValid($page)
    {
        $errorMessage = 'The page was not found.';

        //there is no page
        if ($page === null) {
            throw new NotFoundHttpException($errorMessage);
        }

        $isPublished = $page->isPublished();
        $isPageOwner = $this->get('security.context')->isGranted('PAGE_OWNER', $page);

        //a page not published, not owned, nor granted throw an exception
        if (!$isPublished && !$isPageOwner) {
            throw new NotFoundHttpException($errorMessage);
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
