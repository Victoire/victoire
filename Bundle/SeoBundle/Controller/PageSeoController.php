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
use Victoire\Bundle\SeoBundle\Form\PageSeoType;
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
        $formUrl = $this->get('router')->generate('victoire_seo_pageSeo_settings',
            [
                'id' => $page->getId(),
            ]
        );
        //create the form
        $form = $this->get('form.factory')->create(PageSeoType::class, $pageSeo,
            [
                'action'  => $formUrl,
                'method'  => 'POST',
            ]
        );

        $form->handleRequest($this->get('request'));
        $novalidate = $this->get('request')->query->get('novalidate', false);

        $template = 'VictoireSeoBundle:PageSeo:form.html.twig';
        if ($novalidate === false) {
            $template = 'VictoireSeoBundle:PageSeo:settings.html.twig';
        }
        if (false === $novalidate && $form->isValid()) {
            $em->persist($pageSeo);
            $page->setSeo($pageSeo);
            $em->persist($page);
            $em->flush();

            //redirect to the page url
            if (!method_exists($page, 'getUrl')) {
                $url = $this->generateUrl('victoire_business_template_show', ['id' => $page->getId()]);
            } else {
                /** @var ViewReference $viewReference */
                $viewReference = $this->container->get('victoire_view_reference.repository')
                ->getOneReferenceByParameters(['viewId' => $page->getId()]);

                $page->setReference($viewReference);
                $url = $this->generateUrl('victoire_core_page_show', ['url' => $viewReference->getUrl()]);
            }
            $this->get('victoire_core.current_view')->setCurrentView($page);
            $this->congrat('victoire_seo.save.success');

            return new JsonResponse([
                'success' => true,
                'url'     => $url,
            ]);
        }

        return new JsonResponse([
            'success' => !$form->isSubmitted(),
            'html'    => $this->container->get('templating')->render(
                $template,
                [
                    'page'               => $page,
                    'form'               => $form->createView(),
                    'businessProperties' => $businessProperties,
                ]
            ),
        ]);
    }
}
