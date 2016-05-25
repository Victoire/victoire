<?php

namespace Victoire\Bundle\BlogBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
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
     * Create article.
     *
     * @Route("/create/{id}", name="victoire_blog_article_create")
     * @ParamConverter("blog", class="VictoireBlogBundle:Blog")
     *
     * @return JsonResponse
     */
    public function createAction(Blog $blog)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $article = new Article();
        $article->setBlog($blog);
        $form = $this->createForm(ArticleType::class, $article);

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

            $page = $this->container->get('victoire_business_page.business_page_builder')
                        ->generateEntityPageFromTemplate($article->getTemplate(), $article, $entityManager);
            
            // Transform VBP into BP
            $this->container->get('victoire_business_page.transformer.virtual_to_business_page_transformer')->transform($page);
            $page->setParent($article->getBlog());

            $entityManager->persist($page);
            $entityManager->flush();

            $dispatcher = $this->get('event_dispatcher');
            $event = new ArticleEvent($article);
            $dispatcher->dispatch(VictoireBlogEvents::CREATE_ARTICLE, $event);

            $page->setCurrentLocale($this->get('request')->getLocale());
            $url = $this->container->get('victoire_core.url_builder')->buildUrl($page);
            if (null === $response = $event->getResponse()) {
                $response = new JsonResponse([
                    'success'  => true,
                    'url'      => $this->generateUrl('victoire_core_page_show', ['_locale' => $page->getCurrentLocale(), 'url' => $url]),
                ]);
            }

            return $response;
        } else {
            $formErrorHelper = $this->container->get('victoire_form.error_helper');

            return new JsonResponse(
                [
                    'success' => false,
                    'message' => $formErrorHelper->getRecursiveReadableErrors($form),
                    'html'    => $this->container->get('templating')->render(
                        'VictoireBlogBundle:Article:new.html.twig',
                        [
                            'form'   => $form->createView(),
                            'blogId' => $blog->getId(),
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
        try {
            $form = $this->createForm(ArticleType::class, $article);
        } catch (NoResultException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }

        return new JsonResponse(
            [
                'html' => $this->container->get('templating')->render(
                    'VictoireBlogBundle:Article:new.html.twig',
                    ['form' => $form->createView(), 'blogId' => $blog->getId()]
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
        $form = $this->createForm(ArticleSettingsType::class, $article);
        $pageHelper = $this->get('victoire_page.page_helper');
        $businessProperties = [];

        $businessPage = $pageHelper->findPageByParameters([
            'viewId'   => $article->getTemplate()->getId(),
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
            $template = $article->getTemplate();
            $businessPage->setTemplate($template);
            $page = $pageHelper->findPageByParameters([
                'viewId'   => $template->getId(),
                'entityId' => $article->getId(),
            ]);
            $page->setName($article->getName());
            $page->setSlug($article->getSlug());
            $page->setStatus($article->getStatus());

            $em->flush();
            $page->setReference($this->get('victoire_view_reference.repository')->getOneReferenceByParameters(
                ['viewId' => $page->getId()]
            ));

            $response = [
                'success' => true,
                'url'     => $this->generateUrl('victoire_core_page_show', [
                    '_locale' => $page->getLocale(),
                    'url'     => $page->getReference()->getUrl(),
                ]),
            ];
        } else {
            $template = 'VictoireBlogBundle:Article:';
            $template .= ($novalidate === false) ? 'settings.html.twig' : '_form.html.twig';

            $page = $pageHelper->findPageByParameters([
                'viewId'   => $article->getTemplate()->getId(),
                'entityId' => $article->getId(),
            ]);

            $response = [
                'success' => !$form->isSubmitted(),
                'html'    => $this->get('templating')->render($template, [
                    'action'             => $this->generateUrl('victoire_blog_article_settings', [
                        'id' => $article->getId(),
                    ]),
                    'article'            => $article,
                    'form'               => $form->createView(),
                    'businessProperties' => $businessProperties,
                    'page'               => $page,
                ]),
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
                'templateId' => $article->getTemplate()->getId(),
                'entityId'   => $article->getId(),
            ]
        );

        $blogViewReference = $this->container->get('victoire_view_reference.repository')->getOneReferenceByParameters(
            [
                'viewId' => $article->getBlog()->getId(),
            ]
        );
        $this->get('victoire_blog.manager.article')->delete($article, $bep);

        $message = $this->get('translator')->trans('victoire.blog.article.delete.success', [], 'victoire');
        $this->get('session')->getFlashBag()->add('success', $message);

        $response = [
            'success' => true,
            'url'     => $this->generateUrl('victoire_core_page_show', [
                    'url'     => $blogViewReference->getUrl(),
                ]
            ),
            'message' => $message,
        ];

        return new JsonResponse($response);
    }
}
