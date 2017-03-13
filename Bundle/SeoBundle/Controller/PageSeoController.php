<?php

namespace Victoire\Bundle\SeoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;
use Victoire\Bundle\SeoBundle\Form\PageSeoType;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * The Page Seo controller.
 *
 * @Route("/victoire-dcms/seo")
 */
class PageSeoController extends Controller
{
    use VictoireAlertifyControllerTrait;

    /**
     * Display a form to edit Seo settings.
     *
     * @param Request $request
     * @param View    $view
     *
     * @Route("/{id}/settings", name="victoire_seo_pageSeo_settings")
     * @Method("GET")
     * @Template()
     *
     * @return JsonResponse
     */
    public function settingsAction(Request $request, View $view)
    {
        $pageSeo = $view->getSeo() ?: new PageSeo();
        $form = $this->createSettingsForm($pageSeo, $view);

        $form->handleRequest($request);

        $response = $this->getNotPersistedSettingsResponse(
            $form,
            $view,
            $request->query->get('novalidate', false)
        );

        return new JsonResponse($response);
    }

    /**
     * Save Seo settings.
     *
     * @param Request $request
     * @param View    $view
     *
     * @Route("/{id}/settings", name="victoire_seo_pageSeo_settings_post")
     * @Method("POST")
     * @Template()
     *
     * @return JsonResponse
     */
    public function settingsPostAction(Request $request, View $view)
    {
        $em = $this->getDoctrine()->getManager();

        $pageSeo = $view->getSeo() ?: new PageSeo();
        $form = $this->createSettingsForm($pageSeo, $view);

        $form->handleRequest($request);
        $novalidate = $request->query->get('novalidate', false);

        if (false === $novalidate && $form->isValid()) {
            $em->persist($pageSeo);
            $view->setSeo($pageSeo);
            $em->persist($view);
            $em->flush();

            $this->get('victoire_core.current_view')->setCurrentView($view);
            $this->congrat('victoire_seo.save.success');

            $response = [
                'success' => true,
                'url'     => $this->getViewUrl($view),
            ];
        } else {
            $response = $this->getNotPersistedSettingsResponse($form, $view, $novalidate);
        }

        return new JsonResponse($response);
    }

    /**
     * Create PageSeo Form.
     *
     * @param PageSeo $pageSeo
     * @param View    $view
     *
     * @return FormInterface
     */
    private function createSettingsForm(PageSeo $pageSeo, View $view)
    {
        return $this->get('form.factory')->create(PageSeoType::class, $pageSeo,
            [
                'action' => $this->get('router')->generate('victoire_seo_pageSeo_settings_post',
                    [
                        'id' => $view->getId(),
                    ]
                ),
                'method' => 'POST',
            ]
        );
    }

    /**
     * Get JsonResponse array for Settings novalidate and form display.
     *
     * @param FormInterface $form
     * @param View          $view
     * @param $novalidate
     *
     * @return array
     */
    private function getNotPersistedSettingsResponse(FormInterface $form, View $view, $novalidate)
    {
        $template = sprintf(
            '%s:%s',
            $this->getBaseTemplatePath(),
            ($novalidate === false) ? 'settings.html.twig' : 'form.html.twig'
        );

        return [
            'success' => !$form->isSubmitted(),
            'html'    => $this->container->get('templating')->render(
                $template,
                [
                    'page'               => $view,
                    'form'               => $form->createView(),
                    'businessProperties' => $this->getBusinessProperties($view),
                ]
            ),
        ];
    }

    /**
     * Get url for a View using ViewReferences if necessary.
     *
     * @param View $view
     *
     * @return string
     */
    private function getViewUrl(View $view)
    {
        if (!method_exists($view, 'getUrl')) {
            return $this->generateUrl('victoire_business_template_show', ['id' => $view->getId()]);
        }

        /** @var ViewReference $viewReference */
        $viewReference = $this->container->get('victoire_view_reference.repository')
            ->getOneReferenceByParameters(['viewId' => $view->getId()]);

        $view->setReference($viewReference);

        return $this->generateUrl('victoire_core_page_show', ['url' => $viewReference->getUrl()]);
    }

    /**
     * Return BusinessEntity seaoable properties if View is a BusinessTemplate.
     *
     * @param View $view
     *
     * @return array
     */
    private function getBusinessProperties(View $view)
    {
        $businessProperties = [];

        if ($view instanceof BusinessTemplate) {
            //we can use the business entity properties on the seo
            $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($view->getBusinessEntityId());
            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        return $businessProperties;
    }

    /**
     * @return string
     */
    private function getBaseTemplatePath()
    {
        return 'VictoireSeoBundle:PageSeo';
    }
}
