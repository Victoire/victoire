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
 * Page Administration Controller
 *
 * @Route("/victoire-dcms/page")
 */
class PageAdministrationController extends BasePageController
{
    protected $routes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = array(
            'new'      => 'victoire_core_page_new',
            'show'     => 'victoire_core_page_show',
            'settings' => 'victoire_core_page_settings',
            'detach'   => 'victoire_core_page_detach'
        );
    }

    /**
     * New page
     *
     * @return template
     * @Route("/new", name="victoire_core_page_new", defaults={"isHomepage" : false})
     * @Route("/homepage/new", name="victoire_core_homepage_new", defaults={"isHomepage" : true})
     * @Template()
     *
     * @param boolean $isHomepage Is the page a homepage
     */
    public function newAction($isHomepage = false)
    {

        return new JsonResponse(parent::newAction($isHomepage));
    }

    /**
     * Page settings
     *
     * @Route("/{id}/settings", name="victoire_core_page_settings")
     * @Template()
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     *
     * @param BasePage $page The page
     *
     * @return json The settings
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
     * @Route("/{id}/delete", name="victoire_core_page_delete")
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
     * @Route("/{id}/detach", name="victoire_core_page_detach")
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     */
    public function detachAction(BasePage $page)
    {

    }

    /**
     * Create a Template from a page
     *
     * @param page $page
     * @return template
     * @Route("/{id}/create-template", name="victoire_core_page_createtemplate")
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

            return $this->redirect($this->generateUrl('victoire_core_template_show', array("slug" => $template->getSlug())));
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
     * @Route("/sitemap", name="victoire_core_page_sitemap")
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
                if ($page !== null) {
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

    /**
     * getNewPageType
     *
     * @return string
     */
    protected function getNewPageType()
    {
        return 'victoire_page_type';
    }

    /**
     * getPageSettingsType
     *
     * @return string
     */
    protected function getPageSettingsType()
    {
        return 'victoire_page_settings_type';
    }

    /**
     * getNewPage
     *
     * @return \Victoire\Bundle\PageBundle\Entity\Page
     */
    protected function getNewPage()
    {
        return new Page();
    }

    /**
     * getBaseTemplatePath
     *
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return "VictoirePageBundle:Page";
    }

    /**
     * getRoutes
     *
     * @param string $action
     *
     * @return string The route
     */
    protected function getRoutes($action)
    {
        return $this->routes[$action];
    }
}
