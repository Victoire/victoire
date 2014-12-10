<?php

namespace Victoire\Bundle\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\PageBundle\Controller\BasePageController;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * article Controller
 *
 * @Route("/victoire-dcms/article")
 */
class ArticleController extends BasePageController
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
    public function createAction()
    {
        return new JsonResponse(parent::newAction());
    }

    /**
     * New article
     *
     * @Route("/new/{id}", name="victoire_blog_article_newBlogArticle")
     * @Template()
     *
     * @return JsonResponse
     */
    public function newBlogArticleAction(Blog $blog)
    {
        $article = new Article();
        $article->setBlog($blog);
        $form = $this->container->get('form.factory')->create($this->getNewPageType(), $article);

        return new JsonResponse(
            array(
                'html' => $this->container->get('victoire_templating')->render(
                    $this->getBaseTemplatePath() . ':new.html.twig',
                    array('form' => $form->createView())
                )
            )
        );
    }

    /**
     * Article settings
     *
     * @param Request $request
     * @param Page    $article
     *
     * @Route("/{id}/settings", name="victoire_blog_article_settings")
     *
     * @ParamConverter("article", class="VictoireBlogBundle:Article")
     * @return template
     */
    public function settingsAction(Request $request, BasePage $article)
    {
        $response = parent::settingsAction($request, $article);

        $pattern = $article->getTemplate();

        $page = $this->container->get('victoire_page.page_helper')->findPageByParameters(array(
            'viewId' => $pattern->getId(),
            'locale' => $request->getSession()->get('victoire_locale'),
            'entityId' => $article->getId()
        ));
        $response['url'] = $this->generateUrl('victoire_core_page_show', array('url' => $page->getUrl()));

        return new JsonResponse($response);
    }

    /**
     * Article translation
     *
     * @param Request $request
     * @param Page    $article
     *
     * @Route("/{id}/translate", name="victoire_blog_article_translate")
     *
     * @ParamConverter("article", class="VictoireBlogBundle:Article")
     * @return template
     */
    public function translateAction(Request $request, BasePage $article)
    {
        $response = parent::translateAction($request, $article);

        $pattern = $article->getTemplate();

        $page = $this->container->get('victoire_page.page_helper')->findPageByParameters(array(
            'viewId' => $pattern->getId(),
            'locale' => $request->getSession()->get('victoire_locale'),
            'entityId' => $article->getId()
        ));
        $response['url'] = $this->generateUrl('victoire_core_page_show', array('url' => $page->getUrl()));

        return new JsonResponse($response);
    }

    /**
     * Page delete
     *
     * @param BasePage $article
     *
     * @return template
     * @Route("/{id}/delete", name="victoire_core_article_delete")
     * @Template()
     * @ParamConverter("article", class="VictoireBlogBundle:Article")
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
        return 'victoire_article_settings_type';
    }

    /**
     *
     * @return string
     */
    protected function getPageTranslateType()
    {
        return 'victoire_article_translate_type';
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
