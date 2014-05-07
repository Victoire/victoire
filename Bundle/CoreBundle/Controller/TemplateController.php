<?php

namespace Victoire\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Victoire\Bundle\CoreBundle\Event\Menu\TemplateMenuContextualEvent;
use Victoire\Bundle\PageBundle\Form\TemplateType;
use Victoire\Bundle\PageBundle\Entity\Template as TemplateEntity;


/**
 * Template Controller
 *
 * @Route("template")
 */
class TemplateController extends Controller
{

    /**
     * list of all templates
     *
     * @return response
     * @Route("/index", name="victoire_cms_template_index")
     * @Template()
     */
    public function indexAction()
    {
        $templates = $this->get('doctrine.orm.entity_manager')->getRepository('VictoirePageBundle:Template')->findByParent(null, array('position' => 'ASC'));

        return $this->container->get('victoire_templating')->renderResponse(
            'VictoireCoreBundle:Template:index.html.twig',
            array('templates' => $templates)
        );
    }

    /**
     * create a new Template
     *
     * @return Response
     * @Route("/new", name="victoire_cms_template_new")
     * @Template()
     */
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        $template = new TemplateEntity();
        $form = $this->container->get('form.factory')->create(new TemplateType($em), $template); //TODO utiliser un service

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $em->persist($template);
            $em->flush();

            return $this->redirect($this->generateUrl('victoire_cms_template_show', array("slug" => $template->getSlug())));
        }

        return $this->container->get('victoire_templating')->renderResponse(
            "VictoireCoreBundle:Template:new.html.twig",
            array('form' => $form->createView())
        );
    }

    /**
     * define settings of the template
     *
     * @param string $slug The slug of page
     * @return Response
     * @Route("/{slug}/parametres", name="victoire_cms_template_settings")
     * @Template()
     */
    public function settingsAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $template = $em->getRepository('VictoirePageBundle:Template')->findOneBySlug($slug);

        $templateForm = $this->container->get('form.factory')->create(new TemplateType($em), $template);


        return $this->container->get('victoire_templating')->renderResponse(
            'VictoireCoreBundle:Template:settings.html.twig',
            array('template' => $template,'form' => $templateForm->createView())
        );
    }

    /**
     * show a Template
     *
     * @param Template $template the Template to show
     * @return Response
     * @Route("/{slug}", name="victoire_cms_template_show")
     * @Template()
     * @ParamConverter("template", class="VictoirePageBundle:Template")
     */
    public function showAction($template)
    {

        $event = new TemplateMenuContextualEvent($template);
        $this->get('event_dispatcher')->dispatch('victoire_cms.template_menu.contextual', $event);

        return $this->container->get('victoire_templating')->renderResponse(
            'VictoireCoreBundle:Template:show.html.twig',
            array('template' => $template)
        );
    }

    /**
     * edit a Template
     *
     * @param Template $template The Template to edit
     * @return Response
     * @Route("/edit/{slug}", name="victoire_cms_template_edit")
     * @Template()
     * @ParamConverter("template", class="VictoirePageBundle:Template")
     */
    public function editAction(Template $template)
    {

        $em = $this->getDoctrine()->getManager();
        $form = $this->container->get('form.factory')->create(new TemplateType($em), $template);

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $em->persist($template);
            $em->flush();

            return $this->redirect($this->generateUrl('victoire_cms_template_show', array("slug" => $template->getSlug())));
        }

        return $this->redirect($this->generateUrl('victoire_cms_template_settings', array("slug" => $template->getSlug())));

    }
}
