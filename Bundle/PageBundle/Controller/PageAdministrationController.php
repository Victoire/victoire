<?php

namespace Victoire\Bundle\PageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * Page Administration Controller
 *
 * @Route("/victoire-dcms/page")
 */
class PageAdministrationController extends PageController
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
     * @param boolean $isHomepage Is the page a homepage
     *
     * @Route("/new", name="victoire_core_page_new", defaults={"isHomepage" : false})
     * @Route("/homepage/new", name="victoire_core_homepage_new", defaults={"isHomepage" : true})
     * @Template()
     *
     * @return template
     */
    public function newAction($isHomepage = false)
    {
        return new JsonResponse(parent::newAction($isHomepage));
    }

    /**
     * Page settings
     * @param Request  $request
     * @param BasePage $page
     *
     * @Route("/{id}/settings", name="victoire_core_page_settings")
     * @Template()
     * @ParamConverter("page", class="VictoirePageBundle:Page")
     *
     * @return json The settings
     */
    public function settingsAction(Request $request, BasePage $page)
    {
        return new JsonResponse(parent::settingsAction($request, $page));
    }

    /**
     * Page delete
     * @param BasePage $page
     *
     * @return template
     * @Route("/{id}/delete", name="victoire_core_page_delete")
     * @Template()
     * @ParamConverter("page", class="VictoirePageBundle:Page")
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
     * @param BasePage $page
     *
     * @return template
     * @Route("/{id}/detach", name="victoire_core_page_detach")
     * @ParamConverter("page", class="VictoirePageBundle:Page")
     */
    public function detachAction(BasePage $page)
    {
        throw new \Exception("Not implemented yet");

    }

    /**
     * Show and edit sitemap
     *
     * @Route("/sitemap", name="victoire_core_page_sitemap")
     * @Template()
     * @return template
     */
    public function siteMapAction()
    {
        $em = $this->getDoctrine()->getManager();
        $pageRepo = $em->getRepository('VictoirePageBundle:BasePage');
        $response = array(
            'success' => false
        );
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

            $response = array(
                'success' => true,
                'message' => $this->get('translator')->trans('sitemap.changed.success', array(), 'victoire')
            );
        }

        $pages = $em->getRepository('VictoirePageBundle:BasePage')->findByParent(null, array('position' => 'ASC'));
        $response['html'] = $this->container->get('victoire_templating')->render(
            'VictoirePageBundle:Page:sitemap.html.twig',
            array('pages' => $pages)
        );

        return new JsonResponse($response);
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
