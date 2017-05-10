<?php

namespace Victoire\Bundle\BlogBundle\Controller;

use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\BlogBundle\Event\ArticleEvent;
use Victoire\Bundle\BlogBundle\Form\ArticleSettingsType;
use Victoire\Bundle\BlogBundle\Form\ArticleType;
use Victoire\Bundle\BlogBundle\VictoireBlogEvents;

/**
 * article Controller.
 *
 * @Route("/victoire-dcms/article")
 */
class ArticleController extends Controller
{
    /**
     * Display a form to create a new Blog Article.
     *
     * @Route("/new/{id}", name="victoire_blog_article_new")
     * @Method("GET")
     * @Template()
     *
     * @return JsonResponse
     */
    public function newAction(Blog $blog)
    {
        try {
            $article = new Article();
            $article->setBlog($blog);
            $form = $this->createForm(ArticleType::class, $article);

            return new JsonResponse([
                'html' => $this->container->get('templating')->render(
                    'VictoireBlogBundle:Article:new.html.twig',
                    [
                        'form'   => $form->createView(),
                        'blogId' => $blog->getId(),
                    ]
                ),
            ]);
        } catch (NoResultException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a new Blog Article.
     *
     * @Route("/new/{id}", name="victoire_blog_article_new_post")
     * @Method("POST")
     * @ParamConverter("blog", class="VictoireBlogBundle:Blog")
     *
     * @return JsonResponse
     */
    public function newPostAction(Request $request, Blog $blog)
    {
        $article = new Article();
        $article->setBlog($blog);
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $page = $this->get('victoire_blog.manager.article')->create(
                $article,
                $this->getUser()
            );

            $dispatcher = $this->get('event_dispatcher');
            $event = new ArticleEvent($article);
            $dispatcher->dispatch(VictoireBlogEvents::CREATE_ARTICLE, $event);

            if (null === $response = $event->getResponse()) {
                $response = new JsonResponse([
                    'success' => true,
                    'url'     => $this->generateUrl('victoire_core_page_show', [
                        '_locale' => $request->getLocale(),
                        'url'     => $this->container->get('victoire_core.url_builder')->buildUrl($page),
                    ]),
                ]);
            }

            return $response;
        }

        return new JsonResponse([
            'success' => false,
            'message' => $this->container->get('victoire_form.error_helper')->getRecursiveReadableErrors($form),
            'html'    => $this->container->get('templating')->render(
                'VictoireBlogBundle:Article:new.html.twig',
                [
                    'form'   => $form->createView(),
                    'blogId' => $blog->getId(),
                ]
            ),
        ]);
    }

    /**
     * Display a form to edit Blog Article settings.
     *
     * @param Request $request
     * @param Article $article
     *
     * @Route("/{id}/settings", name="victoire_blog_article_settings")
     * @Method("GET")
     *
     * @ParamConverter("article", class="VictoireBlogBundle:Article")
     *
     * @return JsonResponse
     */
    public function settingsAction(Request $request, Article $article)
    {
        $form = $this->createForm(ArticleSettingsType::class, $article);
        $form->handleRequest($request);

        $response = $this->getNotPersistedSettingsResponse(
            $form,
            $article,
            $request->query->get('novalidate', false)
        );

        return new JsonResponse($response);
    }

    /**
     * Save Blog Article settings.
     *
     * @param Request $request
     * @param Article $article
     *
     * @Route("/{id}/settings", name="victoire_blog_article_settings_post")
     * @Method("POST")
     *
     * @ParamConverter("article", class="VictoireBlogBundle:Article")
     *
     * @return JsonResponse
     */
    public function settingsPostAction(Request $request, Article $article)
    {
        $form = $this->createForm(ArticleSettingsType::class, $article);
        $form->handleRequest($request);

        $novalidate = $request->query->get('novalidate', false);

        if ($novalidate === false && $form->isValid()) {
            $page = $this->get('victoire_blog.manager.article')->updateSettings(
                $article,
                $this->getUser()
            );

            $response = [
                'success' => true,
                'url'     => $this->generateUrl('victoire_core_page_show', [
                    '_locale' => $page->getCurrentLocale(),
                    'url'     => $page->getReference()->getUrl(),
                ]),
            ];
        } else {
            $response = $this->getNotPersistedSettingsResponse($form, $article, $novalidate);
        }

        return new JsonResponse($response);
    }

    /**
     * Delete a BLog Article.
     *
     * @param Article $article
     *
     * @Route("/{id}/delete", name="victoire_core_article_delete")
     * @Template()
     * @ParamConverter("article", class="VictoireBlogBundle:Article")
     *
     * @return JsonResponse
     */
    public function deleteAction(Article $article)
    {
        $blogViewReference = $this->container->get('victoire_view_reference.repository')
            ->getOneReferenceByParameters(['viewId' => $article->getBlog()->getId()]);

        $this->get('victoire_blog.manager.article')->delete($article);

        $message = $this->get('translator')->trans('victoire.blog.article.delete.success', [], 'victoire');
        $this->get('session')->getFlashBag()->add('success', $message);

        $response = [
            'success' => true,
            'url'     => $this->generateUrl('victoire_core_page_show', [
                    'url' => $blogViewReference->getUrl(),
                ]
            ),
            'message' => $message,
        ];

        return new JsonResponse($response);
    }

    /**
     * Get JsonResponse array for Settings novalidate and form display.
     *
     * @param FormInterface $form
     * @param Article       $article
     * @param $novalidate
     *
     * @return array
     */
    private function getNotPersistedSettingsResponse(FormInterface $form, Article $article, $novalidate)
    {
        $template = sprintf(
            '%s:%s',
            $this->getBaseTemplatePath(),
            ($novalidate === false) ? 'settings.html.twig' : '_form.html.twig'
        );

        $page = $this->get('victoire_page.page_helper')->findPageByParameters([
            'viewId'   => $article->getTemplate()->getId(),
            'entityId' => $article->getId(),
        ]);

        return [
            'success' => !$form->isSubmitted(),
            'html'    => $this->get('templating')->render($template, [
                'action' => $this->generateUrl('victoire_blog_article_settings_post', [
                    'id' => $article->getId(),
                ]),
                'article'            => $article,
                'form'               => $form->createView(),
                'businessProperties' => [],
                'page'               => $page,
            ]),
        ];
    }

    /**
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return 'VictoireBlogBundle:Article';
    }
}
