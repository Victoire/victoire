<?php

namespace Victoire\Bundle\SeoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;

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
     * @param page $page
     * @return template
     * @Route("/page/{id}", name="victoire_seo_pageSeo_settings")
     * @Template()
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     */
    public function settingsAction(BasePage $page)
    {
        $em = $this->getDoctrine()->getManager();

        $formFactory = $this->container->get('form.factory');
        $pageSeo = $page->getSeo() ? $page->getSeo() : new PageSeo($page);
        $form = $formFactory->create('seo_page', $pageSeo);

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $em->persist($pageSeo);
            $page->setSeo($pageSeo);
            $em->persist($page);
            $em->flush();

            //redirect to the page url
            $pageUrl = $page->getUrl();

            return new JsonResponse(array(
                'success' => true,
                'url' => $pageUrl
            ));

        }

        return new JsonResponse(array(
            'success' => false,
            'html'    => $this->container->get('victoire_templating')->render(
                'VictoireSeoBundle:PageSeo:settings.html.twig',
                array(
                    'page' => $page,
                    'form' => $form->createView()
                )
            )
        ));
    }
}
