<?php

namespace Victoire\Bundle\PageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Form\PageSettingsType;
use Victoire\Bundle\PageBundle\Form\PageType;

/**
 * @Route("/victoire-dcms/page")
 **/
class PageController extends BasePageController
{
    protected $routes;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->routes = [
            'new'       => 'victoire_core_page_new',
            'show'      => 'victoire_core_page_show',
            'settings'  => 'victoire_core_page_settings',
            'detach'    => 'victoire_core_page_detach',
        ];
    }

    /**
     * New page.
     *
     * @param bool $isHomepage Is the page a homepage
     *
     * @Route("/new", name="victoire_core_page_new", defaults={"isHomepage" : false})
     * @Route("/homepage/new", name="victoire_core_homepage_new", defaults={"isHomepage" : true})
     * @Template()
     *
     * @return JsonResponse
     */
    public function newAction(Request $request, $isHomepage = false)
    {
        return new JsonResponse(parent::newAction($request, $isHomepage));
    }

    /**
     * Page settings.
     *
     * @param Request  $request
     * @param BasePage $page
     *
     * @Route("/{id}/settings", name="victoire_core_page_settings")
     * @Template()
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     *
     * @return JsonResponse The settings
     */
    public function settingsAction(Request $request, BasePage $page)
    {
        return new JsonResponse(parent::settingsAction($request, $page));
    }

    /**
     * Page delete.
     *
     * @param BasePage $page
     *
     * @return JsonResponse
     * @Route("/{id}/delete", name="victoire_core_page_delete")
     * @Template()
     * @ParamConverter("page", class="VictoirePageBundle:BasePage")
     */
    public function deleteAction(BasePage $page)
    {
        return new JsonResponse(parent::deleteAction($page));
    }

    /**
     * getNewPageType.
     *
     * @return string
     */
    protected function getNewPageType()
    {
        return PageType::class;
    }

    /**
     * getPageSettingsType.
     *
     * @return string
     */
    protected function getPageSettingsType()
    {
        return PageSettingsType::class;
    }

    /**
     * getBusinessPageType.
     *
     * @return string
     */
    protected function getBusinessPageType()
    {
        return PageSettingsType::class;
    }

    /**
     * getNewPage.
     *
     * @return \Victoire\Bundle\PageBundle\Entity\Page
     */
    protected function getNewPage()
    {
        return new Page();
    }

    /**
     * getBaseTemplatePath.
     *
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return 'VictoirePageBundle:Page';
    }

    /**
     * getRoutes.
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
