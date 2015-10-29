<?php

namespace Victoire\Bundle\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\BlogBundle\Entity\Tag;
use Victoire\Bundle\BlogBundle\Event\ArticleEvent;
use Victoire\Bundle\BlogBundle\VictoireBlogEvents;

/**
 * article Controller.
 *
 * @Route("/victoire-dcms/article")
 */
class ArticleController extends Controller
{
    /**
     * Create article.
     *
     * @Route("/create", name="victoire_blog_article_create")
     *
     * @return JsonResponse
     */
    public function createAction()
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $article = new Article();
        $form = $this->createForm('victoire_article_type', $article);

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $article->setAuthor($this->getUser());
            $entityManager->persist($article);
            if (is_array($article->getTags())) {
                /** @var Tag $tag */
                foreach ($article->getTags() as $tag) {
                    $tag->setBlog($article->getBlog());
                    $entityManager->persist($tag);
                }
            }

            $entityManager->flush();

            //Auto creation of the BEP
            $page = $this->container->get('victoire_business_page.business_page_builder')
                                ->generateEntityPageFromPattern($article->getPattern(), $article, $entityManager);

            // Transform VBP into BP
            $this->container->get('victoire_business_page.transformer.virtual_to_business_page_transformer')->transform($page);
            $page->setParent($article->getBlog());

            $entityManager->persist($page);
            $entityManager->flush();

            $dispatcher = $this->get('event_dispatcher');
            $event = new ArticleEvent($article);
            $dispatcher->dispatch(VictoireBlogEvents::CREATE_ARTICLE, $event);
            if (null === $response = $event->getResponse()) {
                $response = new JsonResponse([
                    'success'  => true,
                    'url'      => $this->generateUrl('victoire_core_page_show', ['_locale' => $page->getLocale(), 'url' => $page->getUrl()]),
                ]);
            }

            return $response;
        } else {
            $formErrorHelper = $this->container->get('victoire_form.error_helper');

            return new JsonResponse(
                [
                    'success' => false,
                    'message' => $formErrorHelper->getRecursiveReadableErrors($form),
                    'html'    => $this->container->get('victoire_templating')->render(
                        'VictoireBlogBundle:Article:new.html.twig',
                        [
                            'form' => $form->createView(),
                        ]
                    ),
                ]
            );
        }
    }

    /**
     * New article.
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
        $form = $this->createForm('victoire_article_type', $article);

        return new JsonResponse(
            [
                'html' => $this->container->get('victoire_templating')->render(
                    'VictoireBlogBundle:Article:new.html.twig',
                    ['form' => $form->createView()]
                ),
            ]
        );
    }

    /**
     * Article settings.
     *
     * @param Request $request
     * @param Article $article
     *
     * @Route("/{id}/settings", name="victoire_blog_article_settings")
     *
     * @ParamConverter("article", class="VictoireBlogBundle:Article")
     *
     * @return JsonResponse
     */
    public function settingsAction(Request $request, Article $article)
    {
        $form = $this->createForm('victoire_article_settings_type', $article);
        $pageHelper = $this->get('victoire_page.page_helper');
        $businessProperties = [];

        $businessPage = $pageHelper->findPageByParameters([
            'viewId'   => $article->getPattern()->getId(),
            'entityId' => $article->getId(),
        ]);
        $form->handleRequest($request);
        $novalidate = $request->query->get('novalidate', false);

        if ($novalidate === false && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if (count($article->getTags())) {
                /** @var Tag $tag */
                foreach ($article->getTags() as $tag) {
                    $tag->setBlog($article->getBlog());
                    $em->persist($tag);
                }
            }
            $businessPage->setTemplate($article->getPattern());
            $em->flush();

            $pattern = $article->getPattern();

            $page = $pageHelper->findPageByParameters([
                'viewId'   => $pattern->getId(),
                'entityId' => $article->getId(),
            ]);

            $response = [
                'success' => true,
                'url'     => $this->generateUrl('victoire_core_page_show', ['_locale' => $page->getLocale(), 'url' => $page->getUrl()]),
            ];
        } else {
            if ($novalidate === false) {
                $template = 'VictoireBlogBundle:Article:settings.html.twig';
            } else {
                $template = 'VictoireBlogBundle:Article:_form.html.twig';
            }
            $response = [
                'success' => false,
                'html'    => $this->container->get('victoire_templating')->render(
                    $template,
                    [
                        'action'             => $this->generateUrl('victoire_blog_article_settings', ['id' => $article->getId()]),
                        'article'            => $article,
                        'form'               => $form->createView(),
                        'businessProperties' => $businessProperties,
                    ]
                ),
            ];
        }

        return new JsonResponse($response);
    }

    /**
     * Page delete.
     *
     * @param Article $article
     *
     * @return JsonResponse
     * @Route("/{id}/delete", name="victoire_core_article_delete")
     * @Template()
     * @ParamConverter("article", class="VictoireBlogBundle:Article")
     */
    public function deleteAction(Article $article)
    {
        $bep = $this->get('victoire_page.page_helper')->findPageByParameters(
            [
                'patternId' => $article->getPattern()->getId(),
                'entityId'  => $article->getId(),
            ]
        );

        $this->get('victoire_blog.manager.article')->delete($article, $bep);

        //redirect to the homepage
        $homepageUrl = $this->generateUrl('victoire_core_page_show', [
                '_locale' => $article->getBlog()->getLocale(),
                'url'     => $article->getBlog()->getUrl(),
            ]
        );

        $message = $this->get('translator')->trans('victoire.blog.article.delete.success', [], 'victoire');
        $this->get('session')->getFlashBag()->add('success', $message);

        $response = [
            'success' => true,
            'url'     => $homepageUrl,
            'message' => $message,
        ];

        return new JsonResponse($response);
    }
}
