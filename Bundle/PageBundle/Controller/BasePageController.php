<?php
namespace Victoire\Bundle\PageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @return template
     */
    protected function settingsAction(Request $request, BasePage $page, $newTranslation = false)
    {
        $originalPageId = $newTranslation ? $page->getId(): null;
        $entityManager = $this->get('doctrine.orm.entity_manager');

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
            $form->handleRequest($request);

            //the form should be valid
            if ($form->isValid()) {
                if ('true' === $newTranslation) {
                    $targetLocale = $page->getLocale();
                    $this->getDoctrine()->getEntityManager()->refresh($page);
                    $page = $this->get('victoire_core.view_helper')->addTranslation($page, $page->getName().'-'.$targetLocale, $targetLocale);
                    $request->setLocale($targetLocale);
                }
                $entityManager->persist($page);
                $entityManager->flush();

                $response =  array(
                    'success' => true,
                    'url'     => $this->generateUrl('victoire_core_page_show', array('_locale' => $page->getLocale(), 'url' => $page->getUrl()))
                );
            } else {
                $formErrorHelper = $this->get('victoire_form.error_helper');
                $errors = $formErrorHelper->getRecursiveReadableErrors($form);

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
                        'newTranslation' => $newTranslation,
                        'originalPageId' => $originalPageId,
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
