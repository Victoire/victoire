<?php
namespace Victoire\Bundle\PageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * The base page controller is used to interact with all kind of pages
 **/
class BasePageController extends Controller
{

    public function showAction(Request $request, $url)
    {

        $response = $this->container->get('victoire_page.page_helper')->renderPageByUrl($url, $request->getLocale());

        //throw an exception is the page is not valid
        return $response;
    }

    public function showByIdAction($viewId, $entityId = null)
    {
        $page = $this->container->get('victoire_page.page_helper')->findPageByParameters(array(
            'viewId' => $viewId,
            'entityId' => $entityId
        ));
        $this->get('victoire_widget_map.builder')->build($page);

        return $this->redirect($this->generateUrl('victoire_core_page_show', array('url' => $page->getUrl())));
    }
    /**
     * New page
     * @param boolean $isHomepage
     *
     * @return template
     */
    protected function newAction($isHomepage = false)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $page = $this->getNewPage();
        if ($page instanceof Page) {
            $page->setHomepage($isHomepage ? $isHomepage : 0);
        }

        $form = $this->container->get('form.factory')->create($this->getNewPageType(), $page);

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            if ($page->getParent()) {
                $pageNb = count($page->getParent()->getChildren());
            } else {
                $pageNb = count($entityManager->getRepository('VictoirePageBundle:BasePage')->findByParent(null));
            }
            // + 1 because position start at 1, not 0
            $page->setPosition($pageNb + 1);

            $page->setAuthor($this->getUser());
            $entityManager->persist($page);
            $entityManager->flush();

            // If the $page is a BusinessEntity (eg. an Article), compute it's url
            if (null !== $this->container->get('victoire_core.helper.business_entity_helper')->findByEntityInstance($page)) {
                $page = $this->container
                     ->get('victoire_business_entity_page.business_entity_page_helper')
                     ->generateEntityPageFromPattern($page->getTemplate(), $page);
            }

            return array(
                "success"  => true,
                "url"      => $this->generateUrl('victoire_core_page_show', array('url' => $page->getUrl()))
            );
        } else {
            $formErrorHelper = $this->container->get('victoire_form.error_helper');

            return array(
                "success" => false,
                "message" => $formErrorHelper->getRecursiveReadableErrors($form),
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
     * @return array
     */
    protected function settingsAction(Request $request, BasePage $page)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm($this->getPageSettingsType(), $page);
        $businessProperties = array();

        //if the page is a business entity page
        if ($page instanceof BusinessEntityPagePattern) {
            //we can use the business entity properties on the seo
            $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($page->getBusinessEntityName());
            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager->persist($page);
            $entityManager->flush();

             return array(
                'success' => true,
                'url'     => $this->generateUrl('victoire_core_page_show', array('_locale' => $page->getLocale(), 'url' => $page->getUrl()))
            );
        } 
        //we display the form
        $errors = $this->get('victoire_form.error_helper')->getRecursiveReadableErrors($form);

        return  array(
            'success' => empty($errors),
            'html' => $this->container->get('victoire_templating')->render(
                $this->getBaseTemplatePath() . ':settings.html.twig',
                array(
                    'page' => $page,
                    'form' => $form->createView(),
                    'businessProperties' => $businessProperties
                )
            ),
            'message' => $errors
        );
    }


    /**
     * Page translation
     *
     * @param Request  $request
     * @param BasePage $page
     *
     * @return array
     */
    protected function translateAction(Request $request, BasePage $page)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm($this->getPageTranslateType(), $page);

        $businessProperties = array();

        if ($page instanceof BusinessEntityPagePattern) {
            $businessEntityId = $page->getBusinessEntityName();
            $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($businessEntityId);
            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $clone = $this->get('victoire_core.view_helper')->addTranslation($page, $page->getName(), $page->getLocale());
            $entityManager->refresh($page);
            return array(
                'success' => true,
                'url' => $this->generateUrl('victoire_core_page_show', array('_locale'=> $clone->getLocale(), 'url' => $clone->getUrl()))
            );
        }
        $errors = $this->get('victoire_form.error_helper')->getRecursiveReadableErrors($form);

        return array(
            'success' => empty($errors),
            'html' => $this->container->get('victoire_templating')->render(
                $this->getBaseTemplatePath() . ':translate.html.twig',
                array(
                    'page' => $page,
                    'form' => $form->createView(),
                    'businessProperties' => $businessProperties
                )
            ),
            'message' => $errors
        );
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
            $entityManager = $this->get('doctrine.orm.entity_manager');

            //remove the page
            $entityManager->remove($page);

            //flush the modifications
            $entityManager->flush();

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
     * Show homepage or redirect to new page
     *
     * ==========================
     * find homepage
     * if homepage
     *     forward show(homepage)
     * else
         *     redirect to welcome page (dashboard)
     * ==========================
     *
     * @Route("/", name="victoire_core_page_homepage")
     * @return template
     *
     */
    public function homepageAction(Request $request)
    {
        //services
        $entityManager = $this->getDoctrine()->getManager();

        //get the homepage
        $homepage = $entityManager->getRepository('VictoirePageBundle:BasePage')->findOneByHomepage($request->getLocale());

        if ($homepage !== null) {
            return $this->showAction($request, $homepage->getUrl());
        } else {
            return $this->redirect($this->generateUrl('victoire_dashboard_default_welcome'));
        }
    }
}
