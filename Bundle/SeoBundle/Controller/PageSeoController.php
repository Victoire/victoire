<?php

namespace Victoire\Bundle\SeoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;

/**
 *
 * @author Thomas Beaujean <thomas@appventus.com>
 *
 * @Route("/victoire-dcms/seo")
 */
class PageSeoController extends Controller
{
    /**
     * Page settings
     *
     * @param BasePage $page
     * @return template
     * @Route("/page/{id}", name="victoire_seo_pageSeo_settings")
     * @Template()
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     *
     * @return JsonResponse
     */
    public function settingsAction(BasePage $page)
    {
        //services
        $em = $this->getDoctrine()->getManager();
        $formFactory = $this->container->get('form.factory');
        $router = $this->container->get('router');
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');

        $businessProperties = array();

        //if the page is a business entity template page
        if ($page instanceof BusinessEntityTemplatePage) {
            //get the id of the business entity
            $businessEntityId = $page->getBusinessEntityTemplate()->getBusinessEntityName();
            //we can use the business entity properties on the seo
            $businessEntity = $businessEntityHelper->findById($businessEntityId);

            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        //@todo should not the seo be created with the page
        $pageSeo = $page->getSeo() ? $page->getSeo() : new PageSeo($page);

        //url for the form
        $formUrl = $router->generate('victoire_seo_pageSeo_settings',
            array(
                'id' => $page->getId()
            )
        );

        //create the form
        $form = $formFactory->create('seo_page', $pageSeo,
            array(
                'action'  => $formUrl,
                'method' => 'POST'
            )
        );

        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $em->persist($pageSeo);
            $page->setSeo($pageSeo);
            $em->persist($page);
            $em->flush();

            //redirect to the page url
            $pageUrl = $page->getUrl();
            $url = $this->generateUrl('victoire_core_page_show', array('url' => $pageUrl));

            return new JsonResponse(array(
                'success' => true,
                'url' => $url
            ));

        }

        return new JsonResponse(array(
            'success' => false,
            'html'    => $this->container->get('victoire_templating')->render(
                'VictoireSeoBundle:PageSeo:settings.html.twig',
                array(
                    'page'               => $page,
                    'form'               => $form->createView(),
                    'businessProperties' => $businessProperties
                )
            )
        ));
    }
}
