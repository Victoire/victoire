<?php

namespace Victoire\Bundle\TemplateBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\TemplateBundle\Event\Menu\TemplateMenuContextualEvent;

/**
 * Template Controller.
 *
 * @Route("/victoire-dcms/template")
 */
class TemplateController extends Controller
{
    /**
     * list of all templates.
     *
     * @Route("/index", name="victoire_template_index")
     * @Configuration\Template()
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $templates = $this->get('doctrine.orm.entity_manager')->getRepository('VictoireTemplateBundle:Template')->findByTemplate(null, ['position' => 'ASC']);

        return new JsonResponse(
            [
                'success' => true,
                'html'    => $this->container->get('victoire_templating')->render(
                    'VictoireTemplateBundle:Template:index.html.twig',
                    ['templates' => $templates]
                ),
            ]
        );
    }

    /**
     * list of all templates.
     *
     * @param Template $template The template
     *
     * @Route("/show/{slug}", name="victoire_template_show")
     * @ParamConverter("template", class="VictoireTemplateBundle:Template", options={"mapping": {"slug": "slug"}})
     *
     * @return Response
     */
    public function showAction(Template $template)
    {
        //add the view to twig
        $this->get('twig')->addGlobal('view', $template);
        $template->setReference(['id' => $template->getId()]);
        $event = new TemplateMenuContextualEvent($template);

        //TODO : il serait bon de faire des constantes pour les noms d'évents
        $eventName = 'victoire_core.'.Template::TYPE.'_menu.contextual';

        $this->get('event_dispatcher')->dispatch($eventName, $event);

        //the victoire templating
        $victoireTemplating = $this->container->get('victoire_templating');
        $layout = 'AppBundle:Layout:'.$template->getLayout().'.html.twig';

        $parameters = [
            'view'   => $template,
            'id'     => $template->getId(),
            'locale' => $template->getLocale(),
        ];

        $this->get('victoire_widget_map.builder')->build($template);
        $this->container->get('victoire_core.current_view')->setCurrentView($template);

        //create the response
        $response = $victoireTemplating->renderResponse(
            $layout,
            $parameters
        );

        return $response;
    }

    /**
     * create a new Template.
     *
     * @return JsonResponse
     * @Route("/new", name="victoire_template_new")
     * @Configuration\Template()
     */
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        $template = new Template();
        $form = $this->container->get('form.factory')->create($this->getNewTemplateType(), $template); //@todo utiliser un service

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $em->persist($template);
            $em->flush();

            return new JsonResponse([
                'success'  => true,
                'url'      => $this->generateUrl('victoire_template_show', ['slug' => $template->getSlug()]),
            ]);
        }

        return new JsonResponse(
            [
                'success' => true,
                'html'    => $this->container->get('victoire_templating')->render(
                    'VictoireTemplateBundle:Template:new.html.twig',
                    ['form' => $form->createView()]
                ),
            ]
        );
    }

    /**
     * define settings of the template.
     *
     * @param Template $template
     *
     * @return JsonResponse
     * @Route("/{slug}/parametres", name="victoire_template_settings")
     * @ParamConverter("template", class="VictoireTemplateBundle:Template", options={"mapping": {"slug": "slug"}})
     */
    public function settingsAction(Request $request, $template)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm($this->getNewTemplateType(), $template);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($template);
            $em->flush();

            return new JsonResponse(
                    [
                        'success' => true,
                        'url'     => $this->generateUrl('victoire_template_show', ['slug' => $template->getSlug()]),
                    ]
                );
        }

        return new JsonResponse(
            [
                'success' => true,
                'html'    => $this->container->get('victoire_templating')->render(
                    'VictoireTemplateBundle:Template:settings.html.twig',
                    ['template' => $template, 'form' => $form->createView()]
                ),
            ]
        );
    }

    /**
     * translate a template.
     *
     * @param Template $template
     *
     * @return JsonResponse
     * @Route("/{slug}/translate ", name="victoire_template_translate")
     * @ParamConverter("template", class="VictoireTemplateBundle:Template", options={"mapping": {"slug": "slug"}})
     */
    public function translateAction(Request $request, $template)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm($this->getTranslateType(), $template);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $clone = $this->get('victoire_core.view_helper')->addTranslation($template, $template->getName(), $template->getLocale());

            return new JsonResponse(
                [
                    'success' => true,
                    'url'     => $this->generateUrl('victoire_template_show', ['_locale', $clone->getLocale(), 'slug' => $clone->getSlug()]),
                ]
            );
        }

        return new JsonResponse(
            [
                'success' => true,
                'html'    => $this->container->get('victoire_templating')->render(
                    'VictoireTemplateBundle:Template:translate.html.twig',
                    ['template' => $template, 'form' => $form->createView()]
                ),
            ]
        );
    }

    /**
     * edit a Template.
     *
     * @param Template $template The Template to edit
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/edit/{slug}", name="victoire_template_edit")
     * @Configuration\Template()
     * @ParamConverter("template", class="VictoireTemplateBundle:Template")
     */
    public function editAction(Template $template)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->container->get('form.factory')->create($this->getNewTemplateType(), $template);

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $em->persist($template);
            $em->flush();

            return $this->redirect($this->generateUrl('victoire_template_show', ['slug' => $template->getSlug()]));
        }

        return $this->redirect($this->generateUrl('victoire_template_settings', ['slug' => $template->getSlug()]));
    }

    /**
     * get "new" Template Type.
     *
     * @return string
     */
    protected function getNewTemplateType()
    {
        return 'victoire_template_type';
    }

    /**
     * get "new" Template Type.
     *
     * @return string
     */
    protected function getTranslateType()
    {
        return 'victoire_view_translate_type';
    }
}
