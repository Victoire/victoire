<?php

namespace Victoire\Bundle\TemplateBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\TemplateBundle\Event\Menu\TemplateMenuContextualEvent;

/**
 * Template Controller
 *
 * @Route("/victoire-dcms/template")
 */
class TemplateController extends Controller
{
    /**
     * list of all templates
     * @Route("/index", name="victoire_template_index")
     * @Configuration\Template()
     *
     * @return response
     */
    public function indexAction()
    {
        $templates = $this->get('doctrine.orm.entity_manager')->getRepository('VictoireTemplateBundle:Template')->findByTemplate(null, array('position' => 'ASC'));

        return new JsonResponse(
            array(
                "success" => true,
                'html'    => $this->container->get('victoire_templating')->render(
                    'VictoireTemplateBundle:Template:index.html.twig',
                    array('templates' => $templates)
                )
            )
        );
    }

    /**
     * list of all templates
     * @param Victoire\Bundle\TemplateBundle\Entity\Template $template The template
     *
     * @ParamConverter("template", class="VictoireTemplateBundle:Template")
     * @Route("/show/{slug}", name="victoire_template_show")
     *
     * @return Response
     *
     */
    public function showAction(Template $template)
    {
        //add the view to twig
        $this->get('twig')->addGlobal('view', $template);
        $viewParameters = array('viewId' => $template->getId());
        $viewReference = $this->container->get('victoire_core.view_cache_helper')->getReferenceByParameters($viewParameters);
        $template->setReference($viewReference);
        $event = new TemplateMenuContextualEvent($template);

        //TODO : il serait bon de faire des constantes pour les noms d'évents
        $eventName = 'victoire_core.' . Template::TYPE . '_menu.contextual';

        $this->get('event_dispatcher')->dispatch($eventName, $event);

        //the victoire templating
        $victoireTemplating = $this->container->get('victoire_templating');
        $layout = 'AppBundle:Layout:' . $template->getLayout() . '.html.twig';

        $parameters = array(
            'view' => $template,
            'id'   => $template->getId()
        );

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
     * create a new Template
     *
     * @return Response
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

            return new JsonResponse( array(
                "success"  => true,
                "url"      => $this->generateUrl('victoire_template_show', array('slug' => $template->getSlug()))
            ));
        }

        return new JsonResponse(
            array(
                "success" => true,
                'html'    => $this->container->get('victoire_templating')->render(
                    "VictoireTemplateBundle:Template:new.html.twig",
                    array('form' => $form->createView())
                )
            )
        );
    }

    /**
     * define settings of the template
     * @param string $slug The slug of view
     *
     * @return Response
     * @Route("/{slug}/parametres", name="victoire_template_settings")
     * @Configuration\Template()
     */
    public function settingsAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $template = $em->getRepository('VictoireTemplateBundle:Template')->findOneBySlug($slug);

        $templateForm = $this->createForm($this->getNewTemplateType(), $template);

        $templateForm->handleRequest($request);
        if ($templateForm->isValid()) {
                $em->persist($template);
                $em->flush();
                return new JsonResponse(
                    array(
                        'success' => true,
                        "url"     => $this->generateUrl('victoire_template_show', array('slug' => $template->getSlug()))
                    )
                );
        }

        return new JsonResponse(
            array(
                "success" => true,
                'html'    => $this->container->get('victoire_templating')->render(
                    'VictoireTemplateBundle:Template:settings.html.twig',
                    array('template' => $template,'form' => $templateForm->createView())
                )
            )
        );
    }

    /**
     * translate a template 
     * @param string $slug The slug of view
     *
     * @return Response
     * @Route("/{slug}/translate ", name="victoire_template_translate")
     * @Configuration\Template()
     */
    public function translateAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $template = $em->getRepository('VictoireTemplateBundle:Template')->findOneBySlug($slug);

        $templateForm = $this->createForm($this->getNewTemplateType(), $template);

        $templateForm->handleRequest($request);
        if ($templateForm->isValid()) {
            $template = $this->get('victoire_core.view_helper')->addTranslation($template);
            $request->setLocale($page->getLocale());
            $em->persist($template);
            $em->flush();

            return new JsonResponse(
                array(
                    'success' => true,
                    "url"     => $this->generateUrl('victoire_template_show', array('slug' => $template->getSlug()))
                )
            );
        }

        return new JsonResponse(
            array(
                "success" => true,
                'html'    => $this->container->get('victoire_templating')->render(
                    'VictoireTemplateBundle:Template:settings.html.twig',
                    array('template' => $template,'form' => $templateForm->createView())
                )
            )
        );
    }

    /**
     * edit a Template
     * @param Template $template The Template to edit
     *
     * @return Response
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

            return $this->redirect($this->generateUrl('victoire_template_show', array('slug' => $template->getSlug())));
        }

        return $this->redirect($this->generateUrl('victoire_template_settings', array("slug" => $template->getSlug())));

    }

    /**
     * get "new" Template Type
     *
     * @return string
     */
    protected function getNewTemplateType()
    {
        return 'victoire_template_type';
    }
}
