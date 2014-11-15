<?php

namespace Victoire\Bundle\SeoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;

/**
 * The Page seo controller
 *
 * @Route("/victoire-dcms/seo")
 */
class PageSeoController extends Controller
{
    /**
     * BasePage settings
     * @param BasePage $page
     *
     * @Route("/{id}", name="victoire_seo_pageSeo_settings")
     * @Template()
     *
     * @return JsonResponse
     */
    public function settingsAction(BasePage $page)
    {
        //services
        $em = $this->getDoctrine()->getManager();
        
        $businessProperties = array();

        //if the page is a business entity template page
        if ($page instanceof BusinessEntityPage) {
            //get the id of the business entity
            $businessEntityId = $page->getBusinessEntityName();
            //we can use the business entity properties on the seo
            $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($businessEntityId);

            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        $pageSeo = $page->getSeo();

        //url for the form
        $formUrl = $this->container->get('router')->generate('victoire_seo_pageSeo_settings',
            array(
                'id' => $page->getId()
            )
        );

        //create the form
        $form = $this->container->get('form.factory')->create('seo_page', $pageSeo,
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
