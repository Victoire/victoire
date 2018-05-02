<?php

namespace Victoire\Bundle\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\BlogBundle\Entity\BlogCategory;
use Victoire\Bundle\BlogBundle\Form\BlogCategoryType;
use Victoire\Bundle\BlogBundle\Form\BlogSettingsType;
use Victoire\Bundle\BlogBundle\Form\BlogType;
use Victoire\Bundle\BlogBundle\Form\ChooseBlogType;
use Victoire\Bundle\BlogBundle\Repository\BlogRepository;
use Victoire\Bundle\PageBundle\Controller\BasePageController;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * Blog Controller.
 *
 * @Route("/victoire-dcms/blog")
 */
class BlogController extends BasePageController
{
    /**
     * List all Blogs.
     *
     * @Route("/index/{blogId}/{tab}", name="victoire_blog_index", defaults={"blogId" = null, "tab" = "articles"})
     * @ParamConverter("blog", class="VictoireBlogBundle:Blog", options={"id" = "blogId"})
     *
     * @param Request $request
     *
     * @throws \OutOfBoundsException
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request, $blog = null, $tab = 'articles')
    {
        /** @var BlogRepository $blogRepo */
        $blogRepo = $this->get('doctrine.orm.entity_manager')->getRepository('VictoireBlogBundle:Blog');

        // Default value for locale
        $locale = $request->getLocale();

        // Overwrite locale when a locale is chosen in the form
        if ($chooseBlog = $request->request->get('choose_blog')) {
            if (array_key_exists('locale', $chooseBlog)) {
                $locale = $chooseBlog['locale'];
            }
        }

        $parameters = [
            'locale'             => $locale,
            'blog'               => $blog,
            'currentTab'         => $tab,
            'tabs'               => ['articles', 'drafts', 'settings', 'category'],
            'businessProperties' => $blog ? $this->getBusinessProperties($blog) : null,
        ];
        if ($blogRepo->hasMultipleBlog()) {
            $chooseBlogForm = $this->createForm(ChooseBlogType::class, null, [
                'blog'   => $blog,
                'locale' => $locale,
            ]);
            $chooseBlogForm->handleRequest($request);
            $parameters = array_merge(
                $parameters,
                ['chooseBlogForm' => $chooseBlogForm->createView()],
                $chooseBlogForm->getData()
            );
        }

        return $this->render(
            'VictoireBlogBundle:Blog:index.html.twig',
            $parameters
        );
    }

    /**
     * Display Blogs RSS feed.
     *
     * @Route("/feed/{id}.rss", name="victoire_blog_rss", defaults={"_format" = "rss"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function feedAction(Request $request, Blog $blog)
    {
        $articles = $blog->getPublishedArticles();
        if ($categoryId = $request->query->get('category')) {
            $entityManager = $this->getDoctrine()->getManager();
            /** @var Category $category */
            $category = $entityManager->getRepository('VictoireBlogBundle:BlogCategory')->find($categoryId);
            $categoryIds = [];

            function findIds(BlogCategory $category, &$categoryIds)
            {
                $categoryIds[] = $category->getId();

                foreach ($category->getChildren() as $childCategory) {
                    findIds($childCategory, $categoryIds);
                }
            }

            findIds($category, $categoryIds);

            $articles = $articles->filter(function ($article) use ($categoryIds) {
                /* @var Article $article */
                return $article->getCategory() && in_array($article->getCategory()->getId(), $categoryIds, true);
            });
        }

        return $this->render('VictoireBlogBundle:Blog:feed.rss.twig', [
            'blog'     => $blog,
            'articles' => $articles,
        ]);
    }

    /**
     * Display a form to create a new Blog.
     *
     * @Route("/new", name="victoire_blog_new")
     * @Method("GET")
     *
     * @return JsonResponse
     */
    public function newAction(Request $request, $isHomepage = false)
    {
        return new JsonResponse(parent::newAction($request));
    }

    /**
     * Create a new Blog.
     *
     * @Route("/new", name="victoire_blog_new_post")
     * @Method("POST")
     *
     * @return JsonResponse
     */
    public function newPostAction(Request $request)
    {
        return new JsonResponse(parent::newPostAction($request));
    }

    /**
     * Display a form to edit Blog settings.
     *
     * @param Request  $request
     * @param BasePage $blog
     *
     * @Route("/{id}/settings", name="victoire_blog_settings")
     * @Method(methods={"GET", "POST"})
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function settingsAction(Request $request, BasePage $blog)
    {
        $form = $this->getSettingsForm($blog);

        $form->handleRequest($request);
        if ($request->isMethod('POST')) {
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
                $form = $this->getSettingsForm($blog);
            } else {
                $this->warn('error_occured');
            }
        }

        return $this->render(
            'VictoireBlogBundle:Blog/Tabs:_settings.html.twig',
            [
                'blog'               => $blog,
                'form'               => $form->createView(),
                'businessProperties' => [],
            ],
            new Response(null, 200, [
                'X-Inject-Alertify' => true,
            ])
        );
    }

    /**
     * List Blog Categories.
     *
     * @param Request  $request
     * @param BasePage $blog
     *
     * @Route("/{id}/category", name="victoire_blog_category")
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     *
     * @return Response
     */
    public function categoryAction(Request $request, BasePage $blog)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm($this->getPageCategoryType(), $blog);
        $businessProperties = $this->getBusinessProperties($blog);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'url'     => $this->generateUrl('victoire_core_page_show', ['_locale' => $blog->getCurrentLocale(), 'url' => $blog->getUrl()]), ]);
        }
        //we display the form
        $errors = $this->get('victoire_form.error_helper')->getRecursiveReadableErrors($form);
        if ($errors != '') {
            return new JsonResponse(
                [
                    'html' => $this->container->get('templating')->render(
                        'VictoireBlogBundle:Blog:Tabs/_category.html.twig',
                        [
                            'blog'               => $blog,
                            'form'               => $form->createView(),
                            'businessProperties' => $businessProperties,
                        ]
                    ),
                    'message' => $errors,
                ]
            );
        }

        return new Response(
            $this->container->get('templating')->render(
                    $this->getBaseTemplatePath().':Tabs/_category.html.twig',
                    [
                        'blog'               => $blog,
                        'form'               => $form->createView(),
                        'businessProperties' => $businessProperties,
                    ]
                )
        );
    }

    /**
     * List Blog articles.
     *
     * @param Request  $request
     * @param BasePage $blog
     *
     * @Route("/{id}/articles/{articleLocale}", name="victoire_blog_articles")
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function articlesAction(Request $request, BasePage $blog, $articleLocale = null)
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->getArticles($blog);

        return new Response($this->container->get('templating')->render(
            $this->getBaseTemplatePath().':Tabs/_articles.html.twig',
            [
                'locale'    => $articleLocale ? $articleLocale : $request->getLocale(),
                'blog'      => $blog,
                'articles'  => $articles,
            ]
        ));
    }

    /**
     * List Blog drafts.
     *
     * @param Request  $request
     * @param BasePage $blog
     *
     * @Route("/{id}/drafts/{articleLocale}", name="victoire_blog_drafts")
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function draftsAction(Request $request, BasePage $blog, $articleLocale = null)
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->getDrafts($blog);

        return new Response($this->container->get('templating')->render(
            $this->getBaseTemplatePath().':Tabs/_drafts.html.twig',
            [
                'locale'    => $articleLocale ? $articleLocale : $request->getLocale(),
                'blog'      => $blog,
                'articles'  => $articles,
            ]
        ));
    }

    /**
     * Delete a Blog.
     *
     * @param BasePage $blog
     *
     * @Route("/{id}/delete", name="victoire_blog_delete")
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     *
     * @throws \Victoire\Bundle\ViewReferenceBundle\Exception\ViewReferenceNotFoundException
     *
     * @return JsonResponse
     */
    public function deleteAction(BasePage $blog)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_VICTOIRE', $blog)) {
            throw new AccessDeniedException("Nop ! you can't do such an action");
        }

        foreach ($blog->getArticles() as $_article) {
            $bep = $this->get('victoire_page.page_helper')->findPageByParameters(
                [
                    'templateId' => $_article->getTemplate()->getId(),
                    'entityId'   => $_article->getId(),
                ]
            );
            $this->get('victoire_blog.manager.article')->delete($_article, $bep);
        }

        return new JsonResponse(parent::deleteAction($blog));
    }

    /**
     * @return string
     */
    protected function getPageSettingsType()
    {
        return BlogSettingsType::class;
    }

    /**
     * @return string
     */
    protected function getPageCategoryType()
    {
        return BlogCategoryType::class;
    }

    /**
     * @return string
     */
    protected function getNewPageType()
    {
        return BlogType::class;
    }

    /**
     * @return \Victoire\Bundle\BlogBundle\Entity\Blog
     */
    protected function getNewPage()
    {
        return new Blog();
    }

    /**
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return 'VictoireBlogBundle:Blog';
    }

    /**
     * Get Blog from id if defined.
     * If not return the first Blog.
     *
     * @param Request $request
     * @param $blogId
     *
     * @return Blog|false
     */
    protected function getBlog(Request $request, $blogId)
    {
        /** @var BlogRepository $blogRepo */
        $blogRepo = $this->get('doctrine.orm.entity_manager')->getRepository('VictoireBlogBundle:Blog');

        if ($blogId) {
            $blog = $blogRepo->find($blogId);
        } else {
            $blogs = $blogRepo->joinTranslations($request->getLocale())->run();
            $blog = reset($blogs);
        }

        return $blog;
    }

    /**
     * @param Blog $blog
     *
     * @return \Symfony\Component\Form\Form
     */
    private function getSettingsForm(Blog $blog)
    {
        return $this->createForm($this->getPageSettingsType(), $blog, [
            'attr' => [
                'novalidate'   => true,
                'v-ic-post-to' => $this->generateUrl('victoire_blog_settings', [
                    'id' => $blog->getId(),
                ]),
                'v-ic-target' => '#victoire-blog-settings',
            ],
        ]);
    }
}
