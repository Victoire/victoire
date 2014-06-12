<?php

namespace Victoire\Bundle\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Victoire\Bundle\BlogBundle\Entity\Post;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Controller\BasePageController;
use Victoire\Bundle\BlogBundle\Form\ArticleType;
use Victoire\Bundle\BlogBundle\Event\BlogMenuContextualEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * blog Controller
 *
 * @Route("/victoire-dcms/blog")
 */
class ArticleController extends BasePageController
{

    protected $routes;

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
     * @return template
     * @Route("/new", name="victoire_blog_article_new")
     * @Template()
     */
    public function newAction($isHomepage = false)
    {
        return new JsonResponse(parent::newAction());
    }

    /**
     * Article settings
     *
     * @param article $article
     * @return template
     * @Route("/{id}/settings", name="victoire_blog_article_settings")
     * @Template()
     * @ParamConverter("article", class="VictoirePageBundle:BasePage")
     */
    public function settingsAction(BasePage $article)
    {

        return new JsonResponse(parent::settingsAction($article));
    }

    /**
     * Page delete
     *
     * @param article $article
     * @return template
     * @Route("/{id}/delete", name="victoire_core_article_delete")
     * @Template()
     * @ParamConverter("article", class="VictoirePageBundle:BasePage")
     */
    public function deleteAction(BasePage $article)
    {
        if (!$this->get('security.context')->isGranted('PAGE_OWNER', $article)) {
            throw new AccessDeniedException("Nop ! you can't do such an action");
        }

        return new JsonResponse(parent::deleteAction($article));
    }

    protected function getPageSettingsType()
    {
        return 'victoire_article_type';
    }
    protected function getNewPageType()
    {
        return 'victoire_article_type';
    }
    protected function getNewPage()
    {
        return new Article();
    }
    protected function getBaseTemplatePath()
    {
        return "VictoireBlogBundle:Article";
    }
    protected function getRoutes($action)
    {
        return $this->routes[$action];
    }

}
