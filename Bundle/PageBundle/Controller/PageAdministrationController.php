<?php

namespace Victoire\Bundle\PageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
            'new'       => 'victoire_core_page_new',
            'show'      => 'victoire_core_page_show',
            'settings'  => 'victoire_core_page_settings',
            'translate' => 'victoire_core_page_translate',
            'detach'    => 'victoire_core_page_detach',
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
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     *
     * @return json The settings
     */
    public function settingsAction(Request $request, BasePage $page)
    {
        return new JsonResponse(parent::settingsAction($request, $page));
    }

    /**
     * Page translation
     * @param Request  $request
     * @param BasePage $page
     *
     * @Route("/{id}/translate", name="victoire_core_page_translate")
     * @Template()
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     *
     * @return json The settings
     */
    public function translateAction(Request $request, BasePage $page)
    {
        return new JsonResponse(parent::translateAction($request, $page));
    }
    /**
     * Page delete
     * @param BasePage $page
     *
     * @return template
     * @Route("/{id}/delete", name="victoire_core_page_delete")
     * @Template()
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     */
    public function deleteAction(BasePage $page)
    {
        //@todo Disable this since the voter does not work properly
        // if (!$this->get('security.context')->isGranted('PAGE_OWNER', $page)) {
            // throw new AccessDeniedException("Nop ! you can't do such an action");
        // }
        return new JsonResponse(parent::deleteAction($page));
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
     * getPageTranslateType
     *
     * @return string
     */
    protected function getPageTranslateType()
    {
        return 'victoire_view_translate_type';
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
