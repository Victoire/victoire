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
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate;
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
    public function deleteAction(Page $page)
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
        $BusinessEntityTemplate = null;

        //manager
        $manager = $this->getEntityManager();
        $urlHelper = $this->getUrlHelper();
        $PageRepository = $manager->getRepository('VictoirePageBundle:Page');
        $businessEntityTemplateRepository = $manager->getRepository('VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate');
        $routeRepository = $manager->getRepository('VictoireCoreBundle:Route');
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');
        $businessEntityTemplateHelper = $this->get('victoire_business_entity_template.business_entity_template_helper');
        $pageSeoHelper = $this->get('victoire_seo.helper.pageseo_helper');
        $pageHelper = $this->get('victoire_page.page_helper');

        //get the page
        $page = $PageRepository->findOneByUrl($url);

        //we do not try to retrieve an entity for the business entity template page
        if ($page === null) {
            //the exact url was not found

            $shorterUrl = $url;
            $shorterCount = 0;
            $businessEntityTemplate = null;

            $watchDog = 1;

            //until we try to remove all parts
            while ($shorterUrl !== null && $businessEntityTemplate === null) {
                //we remove the last part to look for a business entity template
                $shorterUrl = $urlHelper->removeLastPart($shorterUrl);
                //the number of time the short has been done
                $shorterCount += 1;

                $searchUrl = $shorterUrl;

                //we add the % for the like query
                for ($i = 0; $i < $shorterCount; $i += 1) {
                    $searchUrl .= '/%';
                }

                //we look for a business entity template that looks like this url
                $businessEntityTemplate = $businessEntityTemplateRepository->findOneByLikeUrl($searchUrl);

                //does a template fit the url
                if ($businessEntityTemplate !== null) {
                    //we want the identifier
                    $position = $businessEntityTemplateHelper->getIdentifierPositionInUrl($businessEntityTemplate);

                    if ($position !== null) {
                        $entityIdentifier = $urlHelper->extractPartByPosition($url, $position);
                        //test the entity identifier
                        if ($entityIdentifier === null) {
                            throw new \Exception('The entity identifier could not be retrieved from the url.');
                        }

                        //get the entity
                        $entity = $businessEntityHelper->getEntityByPageAndBusinessIdentifier($businessEntityTemplate, $entityIdentifier);

                        if ($entity === null) {
                            throw new \Exception('The entity with the identifier ['.$entityIdentifier.'] was not found');
                        }

                        //the page is the template found
                        $page = $businessEntityTemplate;
                    } else {
                        throw new \Exception('The template ['.$businessEntityTemplate->getId().'] has no identifier.');
                    }
                }

                //THE watchdog
                $watchDog += 1;

                if ($watchDog > 200) {
                    throw new \Exception('The watchdog has been raised, there might be an infinite loop');
                }
            }
        } else {
            $entity = $page->getEntity();
        }

        //no page were found, we try to look for an BusinessEntityTemplate
        if ($page === null) {
            $page = $BusinessEntityTemplate;
        }

        //override of the seo using the current entity
        if ($page !== null) {
            //only if the page was found
            $pageSeoHelper->updateSeoByEntity($page, $entity);

            //update the parameters of the page
            $pageHelper->updatePageParametersByEntity($page, $entity);
        }

        //no need for this variable anymore
        unset($BusinessEntityTemplate);

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

                $event = new \Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent($page, $entity);

                //TODO : il serait bon de faire des constantes pour les noms d'évents
                $eventName = 'victoire_core.' . $page->getType() . '_menu.contextual';

                $this->get('event_dispatcher')->dispatch($eventName, $event);

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
        $this->isPageValid($page, $entity);

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
     * @param Request  $request
     * @param Page $page
     *
     * @return template
     */
    protected function settingsAction(Request $request, Page $page)
    {
        $em = $this->getEntityManager();

        $response = array();

        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->create($this->getPageSettingsType(), $page);


        //services
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');

        $businessProperties = array();

        //if the page is a business entity template page
        if ($page instanceof BusinessEntityTemplate) {
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
     * If the valid is not valid, an exception is thrown
     *
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

        //if the page is a BusinessEntityTemplate and the entity is not allowed for this template
        if ($page instanceof BusinessEntityTemplate) {
            $hasRoleVictoire = $securityContext->isGranted('ROLE_VICTOIRE');

            //a not victoire user can not access a business template page
            if (!$hasRoleVictoire) {
                throw $this->createAccessDeniedException('You are not allowed to see this page');
            }

            if ($entity !== null) {
                $businessEntityTemplateHelper = $this->get('victoire_business_entity_template.business_entity_template_helper');
                $entityAllowed = $businessEntityTemplateHelper->isEntityAllowed($page, $entity);

                if ($entityAllowed === false) {
                    throw $this->createNotFoundException('The entity ['.$entity->getId().'] is not allowed for the template ['.$page->getId().']');
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
