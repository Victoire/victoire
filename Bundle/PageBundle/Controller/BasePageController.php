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

        //manager
        $manager = $this->getEntityManager();
        $basePageRepository = $manager->getRepository('VictoirePageBundle:BasePage');
        $routeRepository = $manager->getRepository('VictoireCoreBundle:Route');

        //get the page
        $page = $basePageRepository->findOneByUrl($url);

        //no page were found, we try to look for an BusinessEntityTemplatePage
        if ($page === null) {
            $urlMatcher = $this->getGeneralUrlMatcher($url);

            //if the url have been shorten
            if ($urlMatcher !== null) {
                $page = $basePageRepository->findOneByUrl($urlMatcher);

                //a page match the entity template generator
                if ($page) {
                    //we look for the entity
                    $entityId = $this->getEntityIdFromUrl($url);

                    //test the entity id
                    if ($entityId === null) {
                        throw new \Exception('The id could not be retrieved from the url.');
                    }

                    $entity = $this->getEntityByPageAndId($page, $entityId);
                }
            }
        }

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

                $event = new \Victoire\Bundle\CoreBundle\Event\Menu\BasePageMenuContextualEvent($page);
                $this->get('event_dispatcher')->dispatch('victoire_core.' . $page->getType() . '_menu.contextual', $event); //TODO : il serait bon de faire des constantes pour les noms d'évents

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
    protected function settingsAction(BasePage $page)
    {
        $em = $this->getEntityManager();

        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->create($this->getPageSettingsType(), $page);

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $em->persist($page);
            $em->flush();

            return array(
                'success' => true,
                "url"     => $this->generateUrl('victoire_core_page_show', array('url' => $page->getUrl()))
            );

        }

        return array(
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
     * Get the urlMatcher for the template generator
     * It removes what is after the last /
     * and add /{id} to the url
     *
     * @param string $url
     *
     * @return string The url
     */
    protected function getGeneralUrlMatcher($url)
    {
        $urlMatcher = null;

        // split on the / character
        $keywords = preg_split("/\//", $url);

        //if there are some words, we pop the last
        if (count($keywords) > 0) {
            array_pop($keywords);
        }

        //add the id to the end of the url
        array_push($keywords, '{id}');

        //rebuild the url
        $urlMatcher = implode('/', $keywords);

        return $urlMatcher;
    }

    /**
     * Get the entity id from the url
     *
     * @param string $url
     * @return string The id
     */
    protected function getEntityIdFromUrl($url)
    {
        $entityId = null;

        // split on the / character
        $keywords = preg_split("/\//", $url);

        //if there are some words, we pop the last
        if (count($keywords) > 0) {
            $entityId = array_pop($keywords);
        }

        return $entityId;
    }

    /**
     * Get the entity from the page and the id given
     *
     * @param BusinessEntityTemplatePage $page
     * @param string $id
     *
     * @return The entity
     */
    protected function getEntityByPageAndId(BusinessEntityTemplatePage $page, $id)
    {
        $entity = null;

        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');

        $template = $page->getBusinessEntityTemplate();

        $businessEntityId = $template->getBusinessEntityId();

        $businessEntity = $businessEntityHelper->findById($businessEntityId);

        //test the result
        if ($businessEntity === null) {
            throw new \Exception('The business entity ['.$businessEntityId.'] was not found.');
        }

        $entity = $businessEntityHelper->findEntityByBusinessEntityAndId($businessEntity, $id);

        //test the result
        if ($entity === null) {
            throw new \Exception('The entity ['.$id.'] was not found.');
        }

        return $entity;
    }
}
