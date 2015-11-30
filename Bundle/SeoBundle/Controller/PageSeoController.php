<?php

namespace Victoire\Bundle\SeoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * The Page seo controller.
 *
 * @Route("/victoire-dcms/seo")
 */
class PageSeoController extends Controller
{
    use VictoireAlertifyControllerTrait;

    /**
     * BasePage settings.
     *
     * @param BasePage $page
     *
     * @Route("/{id}", name="victoire_seo_pageSeo_settings")
     * @Template()
     *
     * @return JsonResponse
     */
    public function settingsAction(View $page)
    {
        //services
        $em = $this->getDoctrine()->getManager();

        $businessProperties = [];

        //if the page is a business entity template page
        if ($page instanceof BusinessPage || $page instanceof BusinessTemplate) {
            //we can use the business entity properties on the seo
            $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($page->getBusinessEntityId());
            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        $pageSeo = $page->getSeo() ? $page->getSeo() : new PageSeo($page);

        //url for the form
        $formUrl = $this->container->get('router')->generate('victoire_seo_pageSeo_settings',
            [
                'id' => $page->getId(),
            ]
        );
        //create the form
        $form = $this->container->get('form.factory')->create('seo_page', $pageSeo,
            [
                'action'  => $formUrl,
                'method'  => 'POST',
            ]
        );

        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $em->persist($pageSeo);
            $page->setSeo($pageSeo);
            $em->persist($page);
            $em->flush();
            /** @var ViewReference $viewReference */
            $viewReference =  $this->container->get('victoire_view_reference.cache.repository')
                ->getOneReferenceByParameters(['viewId' => $page->getId()]);

            $page->setReference($viewReference);
            $this->get('victoire_core.current_view')->setCurrentView($page);
            $this->congrat('victoire_seo.save.success');

            //redirect to the page url
            if (!method_exists($page, 'getUrl')) {
                $url = $this->generateUrl('victoire_business_template_show', ['id' => $page->getId()]);
            } else {
                $url = $this->generateUrl('victoire_core_page_show', ['url' => $viewReference->getUrl()]);
            }

            return new JsonResponse([
                'success' => true,
                'url'     => $url,
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'html'    => $this->container->get('victoire_templating')->render(
                'VictoireSeoBundle:PageSeo:settings.html.twig',
                [
                    'page'               => $page,
                    'form'               => $form->createView(),
                    'businessProperties' => $businessProperties,
                ]
            ),
        ]);
    }
}
