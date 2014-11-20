<?php

namespace Victoire\Bundle\I18nBundle\Controller;

use AppVentus\Awesome\ShortcutsBundle\Controller\AwesomeController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;

use Victoire\Bundle\PageBundle\Entity\BasePage;

class I18nController extends AwesomeController
{
	/**
     * @param Request  $request
     * @param BasePage $page
     *
     * @Route("/addTranslation/{page}", name="victoire_i18n_page_translation")
     * @return template
     */
    public function addTranslationAction(Request $request, BasePage $page)
    {
        $originalPageId = $page->getId();
        $em = $this->getEntityManager();

        $response = array();

        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->create('victoire_page_settings_type',  $page);

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

                $translatedPage = clone $page;
                $em->persist($translatedPage);
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
                    'VictoireI18nBundle:I18n:addtranslation.html.twig',
                    array(
                        'id' => $originalPageId,
                        'page' => $page,
                        'form' => $form->createView(),
                        'businessProperties' => $businessProperties
                    )
                )
            );
        }

        return new JsonResponse($response);
    }
}
