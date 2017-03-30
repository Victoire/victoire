<?php

namespace Victoire\Bundle\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Victoire\Bundle\BlogBundle\Entity\Blog;
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
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \OutOfBoundsException
     */
    public function indexAction(Request $request, $blog = null, $tab = 'articles')
    {
        /** @var BlogRepository $blogRepo */
        $blogRepo = $this->get('doctrine.orm.entity_manager')->getRepository('VictoireBlogBundle:Blog');
        $locale = $request->getLocale();
        $parameters = [
            'locale'             => $locale,
            'blog'               => $blog,
            'currentTab'         => $tab,
            'tabs'               => ['articles', 'settings', 'category'],
            'businessProperties' => $blog ? $this->getBusinessProperties($blog) : null,
        ];
        if($blogRepo->needChooseForm())
        {
            $chooseBlogForm = $this->createForm(ChooseBlogType::class, null, [
                'blog'   => $blog,
                'locale' => $locale
            ]);
            $chooseBlogForm->handleRequest($request);
            $data = $chooseBlogForm->getData();
            $parameters = array_merge($parameters, [
                'chooseBlogForm'    => $chooseBlogForm->createView()
            ], $data);
        }

        return new JsonResponse(
            [
                'html' => $this->container->get('templating')->render(
                    $this->getBaseTemplatePath().':index.html.twig',$parameters
                ),
            ]
        );
    }

    /**
     * Display Blogs RSS feed.
     *
     * @Route("/feed/{id}.rss", name="victoire_blog_rss", defaults={"_format" = "rss"})
     *
     * @param Request $request
     * @Template("VictoireBlogBundle:Blog:feed.rss.twig")
     *
     * @return array
     */
    public function feedAction(Request $request, Blog $blog)
    {
        return [
            'blog' => $blog,
        ];
    }

    /**
     * Display a form to create a new Blog.
     *
     * @Route("/new", name="victoire_blog_new")
     * @Method("GET")
     * @Template()
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
     * @Template()
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
     * @param Request $request
     * @param BasePage $blog
     *
     * @Route("/{id}/settings", name="victoire_blog_settings")
     * @Method("GET")
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function settingsAction(Request $request, BasePage $blog)
    {
        $form = $this->createForm($this->getPageSettingsType(), $blog);

        $form->handleRequest($request);

        return new Response($this->container->get('templating')->render(
            $this->getBaseTemplatePath().':Tabs/_settings.html.twig',
            [
                'blog'               => $blog,
                'form'               => $form->createView(),
                'businessProperties' => [],
            ]
        ));
    }

    /**
     * Save Blog settings.
     *
     * @Route("/{id}/settings", name="victoire_blog_settings_post")
     * @Method("POST")
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     *
     * @return JsonResponse
     */
    protected function settingsPostAction(Request $request, BasePage $blog)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm($this->getPageSettingsType(), $blog);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            return new JsonResponse($this->getViewReferenceRedirect($request, $blog));
        }

        return new JsonResponse([
            'success' => false,
            'message' => $this->get('victoire_form.error_helper')->getRecursiveReadableErrors($form),
            'html'    => $this->container->get('templating')->render(
                $this->getBaseTemplatePath().':Tabs/_settings.html.twig',
                [
                    'blog'               => $blog,
                    'form'               => $form->createView(),
                    'businessProperties' => [],
                ]
            ),
        ]);
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
            return new JsonResponse(['html' => $this->container->get('templating')->render(
                        $this->getBaseTemplatePath().':Tabs/_category.html.twig',
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

        return new Response($this->container->get('templating')->render(
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
     * @param Request $request
     * @param BasePage $blog
     *
     * @Route("/{id}/articles/{articleLocale}", name="victoire_blog_articles")
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function articlesAction(Request $request, BasePage $blog, $articleLocale = null)
    {
        return new Response($this->container->get('templating')->render(
            $this->getBaseTemplatePath().':Tabs/_articles.html.twig',
            [
                'locale' => $articleLocale ? $articleLocale: $request->getLocale(),
                'blog' => $blog,
            ]
        ));
    }

    /**
     * Delete a Blog.
     *
     * @param BasePage $blog
     *
     * @Route("/{id}/delete", name="victoire_blog_delete")
     * @Template()
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
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
}
