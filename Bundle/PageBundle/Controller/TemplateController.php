<?php

namespace Victoire\Bundle\PageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Victoire\Bundle\PageBundle\Entity\Template as TemplateEntity;
use Victoire\Bundle\PageBundle\Event\Menu\TemplateMenuContextualEvent;
use Victoire\Bundle\PageBundle\Form\TemplateType;


/**
 * Template Controller
 *
 * @Route("/victoire-dcms/template")
 */
class TemplateController extends Controller
{
    /**
     * list of all templates
     *
     * @return response
     * @Route("/index", name="victoire_core_template_index")
     * @Template()
     */
    public function indexAction()
    {
        $templates = $this->get('doctrine.orm.entity_manager')->getRepository('VictoirePageBundle:Template')->findByParent(null, array('position' => 'ASC'));

        return new JsonResponse(
            array(
                "success" => true,
                'html'    => $this->container->get('victoire_templating')->render(
                    'VictoirePageBundle:Template:index.html.twig',
                    array('templates' => $templates)
                )
            )
        );
    }

    /**
     * create a new Template
     *
     * @return Response
     * @Route("/new", name="victoire_core_template_new")
     * @Template()
     */
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        $template = new TemplateEntity();
        $form = $this->container->get('form.factory')->create($this->getNewTemplateType(), $template); //TODO utiliser un service

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $em->persist($template);
            $em->flush();

            return new JsonResponse( array(
                "success"  => true,
                "url"      => $this->generateUrl('victoire_core_page_show', array('url' => $template->getUrl()))
            ));
        }

        return new JsonResponse(
            array(
                "success" => true,
                'html'    => $this->container->get('victoire_templating')->render(
                    "VictoirePageBundle:Template:new.html.twig",
                    array('form' => $form->createView())
                )
            )
        );
    }

    /**
     * define settings of the template
     *
     * @param string $slug The slug of page
     * @return Response
     * @Route("/{slug}/parametres", name="victoire_core_template_settings")
     * @Template()
     */
    public function settingsAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $template = $em->getRepository('VictoirePageBundle:Template')->findOneBySlug($slug);

        $templateForm = $this->container->get('form.factory')->create($this->getNewTemplateType(), $template);


        $templateForm->handleRequest($this->get('request'));
        if ($templateForm->isValid()) {
            $em->persist($template);
            $em->flush();

            return new JsonResponse(
                    array(
                    'success' => true,
                    "url"     => $this->generateUrl('victoire_core_page_show', array('url' => $template->getUrl()))
                )
            );

        }

        return new JsonResponse(
            array(
                "success" => true,
                'html'    => $this->container->get('victoire_templating')->render(
                    'VictoirePageBundle:Template:settings.html.twig',
                    array('page' => $template,'form' => $templateForm->createView())
                )
            )
        );
    }

    /**
     * edit a Template
     *
     * @param Template $template The Template to edit
     * @return Response
     * @Route("/edit/{slug}", name="victoire_core_template_edit")
     * @Template()
     * @ParamConverter("template", class="VictoirePageBundle:Template")
     */
    public function editAction(Template $template)
    {

        $em = $this->getDoctrine()->getManager();
        $form = $this->container->get('form.factory')->create($this->getNewTemplateType(), $template);

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $em->persist($template);
            $em->flush();

            return $this->redirect($this->generateUrl('victoire_core_page_show', array('url' => $template->getUrl())));
        }

        return $this->redirect($this->generateUrl('victoire_core_template_settings', array("slug" => $template->getSlug())));

    }

    /**
     * getNewPageType
     *
     * @return string
     */
    protected function getNewTemplateType()
    {
        return 'victoire_template_type';
    }
}
