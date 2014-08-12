<?php

namespace Victoire\Bundle\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\PageBundle\Controller\PageController;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * blog Controller
 *
 * @Route("/victoire-dcms/blog")
 */
class ArticleController extends PageController
{
    protected $routes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = array(
                'new'      => 'victoire_blog_article_new',
                'show'     => 'victoire_core_page_show',
                'settings' => 'victoire_blog_article_settings',
            );
    }

    /**
     * New page
     *
     * @Route("/new", name="victoire_blog_article_new")
     * @Template()
     *
     * @return JsonResponse
     */
    public function newAction($isHomepage = false)
    {
        return new JsonResponse(parent::newAction());
    }

    /**
     * Article settings
     *
     * @param Request $request
     * @param Page    $article
     *
     * @return template
     * @Route("/{id}/settings", name="victoire_blog_article_settings")
     * @Template()
     * @ParamConverter("article", class="VictoirePageBundle:Page")
     */
    public function settingsAction(Request $request, BasePage $article)
    {
        return new JsonResponse(parent::settingsAction($article));
    }

    /**
     * Page delete
     *
     * @param BasePage $article
     *
     * @return template
     * @Route("/{id}/delete", name="victoire_core_article_delete")
     * @Template()
     * @ParamConverter("article", class="VictoirePageBundle:Page")
     */
    public function deleteAction(BasePage $article)
    {
        if (!$this->get('security.context')->isGranted('PAGE_OWNER', $article)) {
            throw new AccessDeniedException("Nop ! you can't do such an action");
        }

        return new JsonResponse(parent::deleteAction($article));
    }

    /**
     *
     * @return string
     */
    protected function getPageSettingsType()
    {
        return 'victoire_article_type';
    }

    /**
     *
     * @return string
     */
    protected function getNewPageType()
    {
        return 'victoire_article_type';
    }

    /**
     *
     * @return \Victoire\Bundle\BlogBundle\Entity\Article
     */
    protected function getNewPage()
    {
        return new Article();
    }

    /**
     *
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return "VictoireBlogBundle:Article";
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
