<?php

namespace Victoire\Bundle\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\PageBundle\Controller\BasePageController;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * blog Controller
 *
 * @Route("/victoire-dcms/blog")
 */
class BlogController extends BasePageController
{
    protected $routes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = array(
            'new'       => 'victoire_blog_index',
            'show'      => 'victoire_core_page_show',
            'settings'  => 'victoire_blog_settings',
            'translate' => 'victoire_blog_translate',
            'delete'    => 'victoire_blog_delete',
        );
    }

    /**
     * New page
     *
     * @Route("/", name="victoire_blog_index")
     * @Template()
     *
     * @return JsonResponse
     */
    public function indexAction($isHomepage = false)
    {
        $blogs = $this->get('doctrine.orm.entity_manager')
            ->getRepository('VictoireBlogBundle:Blog')
            ->getAll()->run();

        return new JsonResponse(
            array(
                'html' => $this->container->get('victoire_templating')->render(
                    $this->getBaseTemplatePath() . ':index.html.twig', array(
                        'blogs' => $blogs
                    )
                )
            )
        );
    }

    /**
     * New page
     *
     * @Route("/new", name="victoire_blog_new")
     * @Template()
     *
     * @return JsonResponse
     */
    public function newAction($isHomepage = false)
    {
        return new JsonResponse(parent::newAction());
    }

    /**
     * Blog settings
     *
     * @param Request $request
     * @param BasePage    $blog
     *
     * @return JsonResponse
     * @Route("/{id}/settings", name="victoire_blog_settings")
     * @Template()
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     */
    public function settingsAction(Request $request, BasePage $blog)
    {
        return new JsonResponse(parent::settingsAction($request, $blog));
    }

    /**
     * Blog translation
     *
     * @param Request $request
     * @param BasePage    $blog
     *
     * @return JsonResponse
     * @Route("/{id}/translate", name="victoire_blog_translate")
     * @Template()
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     */
    public function translateAction(Request $request, BasePage $blog)
    {
        return new JsonResponse(parent::translateAction($request, $blog));
    }

    /**
     * Page delete
     *
     * @param Blog $blog
     *
     * @return JsonResponse
     * @Route("/{id}/delete", name="victoire_blog_delete")
     * @Template()
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     */
    public function deleteAction(BasePage $blog)
    {
        if (!$this->get('security.context')->isGranted('PAGE_OWNER', $blog)) {
            throw new AccessDeniedException("Nop ! you can't do such an action");
        }

        return new JsonResponse(parent::deleteAction($blog));
    }

    /**
     *
     * @return string
     */
    protected function getPageSettingsType()
    {
        return 'victoire_blog_settings_type';
    }

    /**
     *
     * @return string
     */
    protected function getPageTranslateType()
    {
        return 'victoire_view_translate_type';
    }

    /**
     *
     * @return string
     */
    protected function getNewPageType()
    {
        return 'victoire_blog_type';
    }

    /**
     *
     * @return \Victoire\Bundle\BlogBundle\Entity\Blog
     */
    protected function getNewPage()
    {
        return new Blog();
    }

    /**
     *
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return "VictoireBlogBundle:Blog";
    }

    /**
     *
     * @param unknown $action
     */
    protected function getRoutes($action)
    {
        return $this->routes[$action];
    }
}
