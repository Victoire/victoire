<?php

namespace Victoire\Bundle\PageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Victoire\Bundle\CoreBundle\Form\TemplateType;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Form\PageSettingsType;
use Victoire\Bundle\PageBundle\Form\PageType;


/**
 * Page controller
 *
 */
class PageController extends BasePageController
{

    protected $routes;

    public function __construct()
    {
        $this->routes = array(
            'new'      => 'victoire_cms_page_new',
            'show'     => 'victoire_cms_page_show',
            'settings' => 'victoire_cms_page_settings',
            'detach'   => 'victoire_cms_page_detach'
        );
    }

    /**
     * Show homepage or redirect to new page
     *
     * ==========================
     * find homepage
     * if homepage
     *     forward show(homepage)
     * else
     *     redirect to welcome page (dashboard)
     * ==========================
     *
     * @Route("/", name="victoire_cms_page_homepage")
     * @return template
     *
     */
    public function homepageAction()
    {
        $homepage = $this->getDoctrine()->getManager()->getRepository('VictoirePageBundle:Page')->findOneByHomepage(true);

        if ($homepage) {
            return $this->showAction($homepage->getUrl());
        } else {
            return $this->redirect($this->generateUrl('victoire_dashboard_default_welcome'));
        }
    }

    /**
     * New page
     *
     * @return template
     * @Route("/page/new", name="victoire_cms_page_new", defaults={"isHomepage" : false})
     * @Route("/homepage/new", name="victoire_cms_homepage_new", defaults={"isHomepage" : true})
     * @Template()
     */
    public function newAction($isHomepage = false)
    {

        return new JsonResponse(parent::newAction($isHomepage));
    }

    /**
     * Page settings
     *
     * @param page $page
     * @return template
     * @Route("/page/{id}/settings", name="victoire_cms_page_settings")
     * @Template()
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     */
    public function settingsAction(BasePage $page)
    {

        return new JsonResponse(parent::settingsAction($page));
    }

    /**
     * Page delete
     *
     * @param page $page
     * @return template
     * @Route("/page/{id}/delete", name="victoire_cms_page_delete")
     * @Template()
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     */
    public function deleteAction(BasePage $page)
    {
        if (!$this->get('security.context')->isGranted('PAGE_OWNER', $page)) {
            throw new AccessDeniedException("Nop ! you can't do such an action");
        }

        return new JsonResponse(parent::deleteAction($page));
    }


    /**
     * Detach a page from a template
     *
     * @param page $page
     * @return template
     * @Route("/page/{id}/detach", name="victoire_cms_page_detach")
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     */
    public function detachAction(BasePage $page)
    {
        // $template = $page->getTemplate();
        // $em = $this->getDoctrine()->getManager();

        // foreach ($page->getWidgets() as $widget) {
        //     if ($widget instanceof WidgetReference) {
        //         $em->remove($widget);
        //     }
        // }

        // $widgets = $template->getWidgets();
        // $pageWidgets = array();

        // foreach ($widgets as $widget) {
        //     $pageWidget = clone $widget;
        //     $pageWidgets[] = $pageWidget;
        //     $em->persist($pageWidget);
        // }
        // //associate template's widgets to our standalone page and detach page from template
        // $page->setWidgets($pageWidgets);
        // $page->setTemplate(null);

        // $em->persist($page);
        // $em->flush();

        // return $this->redirect($this->generateUrl($this->getRoutes('show'), array('slug' => $page->getSlug())));

    }

    /**
     * Create a Template from a page
     *
     * @param page $page
     * @return template
     * @Route("/page/{id}/create-template", name="victoire_cms_page_createtemplate")
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     */
    public function createTemplateAction(BasePage $page)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->container->get('form.factory')->create(new TemplateType($em), $page);
        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $template = $form->getData();

            $em->remove($page);
            $em->persist($template);
            $em->flush();

            return $this->redirect($this->generateUrl('victoire_cms_template_show', array("slug" => $template->getSlug())));
        }

        return $this->container->get('victoire_templating')->renderResponse(
            'VictoirePageBundle:Page:settings.html.twig',
            array('page' => $page, 'form' => $form->createView())
        );

    }

    /**
     * Show and edit sitemap
     *
     * @return template
     * @Route("/page/sitemap", name="victoire_cms_page_sitemap")
     * @Template()
     */
    public function siteMapAction()
    {
        $em = $this->getDoctrine()->getManager();
        $pageRepo = $em->getRepository('VictoirePageBundle:Page');
        if ($this->get('request')->getMethod() === "POST") {
            $sorted = $this->getRequest()->request->get('sorted');
            $depths = array();
            //reorder pages positions
            foreach ($sorted as $item) {
                $depths[$item['depth']][$item['item_id']] = 1;
                $page = $pageRepo->findOneById($item['item_id']);
                if ( $page !== null) {
                    if ($item['parent_id'] !== '') {
                        $parent = $pageRepo->findOneById($item['parent_id']);
                        $page->setParent($parent);
                    } else {
                        $page->setParent(null);
                    }
                    $page->setPosition(count($depths[$item['depth']]));
                    $em->persist($page);
                }
            }
            $em->flush();

            return new Response();
        } else {
            $pages = $em->getRepository('VictoirePageBundle:Page')->findByParent(null, array('position' => 'ASC'));

            return new JsonResponse(array(
                'html' => $this->container->get('victoire_templating')->render(
                    'VictoirePageBundle:Page:sitemap.html.twig',
                    array('pages' => $pages)
                ),
                'success' => false
            ));
        }
    }

    protected function getNewPageType()
    {
        return 'victoire_page_type';
    }
    protected function getPageSettingsType()
    {
        return 'victoire_page_settings_type';
    }
    protected function getNewPage()
    {
        return new Page();
    }
    protected function getBaseTemplatePath()
    {
        return "VictoirePageBundle:Page";
    }
    protected function getRoutes($action)
    {
        return $this->routes[$action];
    }
}
