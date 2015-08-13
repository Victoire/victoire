<?php

namespace Victoire\Bundle\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\BlogBundle\Repository\BlogRepository;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\PageBundle\Controller\BasePageController;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\BlogBundle\Form\ChooseBlogType;

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
            'new'       => 'victoire_blog_new',
            'show'      => 'victoire_core_page_show',
            'settings'  => 'victoire_blog_settings',
            'articles'  => 'victoire_blog_articles',
            'category'  => 'victoire_blog_category',
            'translate' => 'victoire_blog_translate',
            'delete'    => 'victoire_blog_delete',
        );
    }

    /**
     * New page
     *
     * @Route("/index/{blogId}/{tab}", name="victoire_blog_index", defaults={"blogId" = null, "tab" = "articles"})
     * @param Request $request
     * @param integer|null $id
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request, $blogId = null, $tab = 'articles')
    {
        /** @var BlogRepository $blogRepo */
        $blogRepo = $this->get('doctrine.orm.entity_manager')->getRepository('VictoireBlogBundle:Blog');
        $blogs = $blogRepo->getAll()->run();
        $blog = reset($blogs);
        if (is_numeric($blogId)) {
            $blog = $blogRepo->find($blogId);
        }
        $options['blog'] = $blog;
        $template = $this->getBaseTemplatePath().':index.html.twig';
        $chooseBlogForm = $this->createForm(new ChooseBlogType(), null, $options);

        $chooseBlogForm->handleRequest($request);
        if ($chooseBlogForm->isValid()) {
            $blog = $chooseBlogForm->getData()['blog'];
            $template = $this->getBaseTemplatePath().':_blogItem.html.twig';
            $chooseBlogForm = $this->createForm(new ChooseBlogType(), null, array('blog' => $blog));
        }
        $businessProperties = array();

        if ($blog instanceof BusinessEntityPagePattern) {
            //we can use the business entity properties on the seo
            $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($blog->getBusinessEntityId());
            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        return new JsonResponse(
            array(
                'html' => $this->container->get('victoire_templating')->render(
                    $template,
                    array(
                        'blog'               => $blog,
                        'currentTab'         => $tab,
                        'tabs'               =>  array('articles', 'settings', 'category'),
                        'chooseBlogForm'     => $chooseBlogForm->createView(),
                        'businessProperties' => $businessProperties,
                    )
                ),
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
     * @param Request  $request
     * @param BasePage $blog
     *
     * @return JsonResponse
     * @Route("/{id}/settings", name="victoire_blog_settings")
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     */
    public function settingsAction(Request $request, BasePage $blog)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm($this->getPageSettingsType(), $blog);
        $businessProperties = array();

        //if the page is a business entity page
        if ($blog instanceof BusinessEntityPagePattern) {
            //we can use the business entity properties on the seo
            $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($blog->getBusinessEntityId());
            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            return new JsonResponse(array(
                'success' => true,
                'url' => $this->generateUrl('victoire_core_page_show', array('_locale' => $blog->getLocale(), 'url' => $blog->getUrl())),));
        }
        //we display the form
        $errors = $this->get('victoire_form.error_helper')->getRecursiveReadableErrors($form);
        if ($errors != '') {
            return new JsonResponse(array('html' => $this->container->get('victoire_templating')->render(
                        $this->getBaseTemplatePath().':Tabs/_settings.html.twig',
                            array(
                                'blog' => $blog,
                                'form' => $form->createView(),
                                'businessProperties' => $businessProperties,
                            )
                        ),
                        'message' => $errors,
                    )
                );
        }

        return new Response($this->container->get('victoire_templating')->render(
                    $this->getBaseTemplatePath().':Tabs/_settings.html.twig',
                    array(
                        'blog' => $blog,
                        'form' => $form->createView(),
                        'businessProperties' => $businessProperties,
                    )
                )
        );
    }

    /**
     * Blog settings
     *
     * @param Request  $request
     * @param BasePage $blog
     *
     * @return Response
     * @Route("/{id}/category", name="victoire_blog_category")
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     */
    public function categoryAction(Request $request, BasePage $blog)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm($this->getPageCategoryType(), $blog);
        $businessProperties = array();

        //if the page is a business entity page
        if ($blog instanceof BusinessEntityPagePattern) {
            //we can use the business entity properties on the seo
            $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($blog->getBusinessEntityId());
            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            return new JsonResponse(array(
                'success' => true,
                'url' => $this->generateUrl('victoire_core_page_show', array('_locale' => $blog->getLocale(), 'url' => $blog->getUrl())),));
        }
        //we display the form
        $errors = $this->get('victoire_form.error_helper')->getRecursiveReadableErrors($form);
        if ($errors != '') {
            return new JsonResponse(array('html' => $this->container->get('victoire_templating')->render(
                        $this->getBaseTemplatePath().':Tabs/_category.html.twig',
                            array(
                                'blog' => $blog,
                                'form' => $form->createView(),
                                'businessProperties' => $businessProperties,
                            )
                        ),
                        'message' => $errors,
                    )
                );
        }

        return new Response($this->container->get('victoire_templating')->render(
                    $this->getBaseTemplatePath().':Tabs/_category.html.twig',
                    array(
                        'blog' => $blog,
                        'form' => $form->createView(),
                        'businessProperties' => $businessProperties,
                    )
                )
        );
    }

    /**
     * Blog settings
     *
     * @param Request  $request
     * @param BasePage $blog
     *
     * @return Response
     * @Route("/{id}/articles", name="victoire_blog_articles")
     * @ParamConverter("blog", class="VictoirePageBundle:BasePage")
     */
    public function articlesAction(Request $request, BasePage $blog)
    {
        return new Response($this->container->get('victoire_templating')->render(
                    $this->getBaseTemplatePath().':Tabs/_articles.html.twig',
                    array(
                        'blog' => $blog,
                    )
                )
        );
    }

    /**
     * Blog translation
     *
     * @param Request  $request
     * @param BasePage $blog
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
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_VICTOIRE', $blog)) {
            throw new AccessDeniedException("Nop ! you can't do such an action");
        }

        foreach ($blog->getArticles() as $_article) {
            $bep = $this->get('victoire_page.page_helper')->findPageByParameters(
                array(
                    'patternId' => $_article->getPattern()->getId(),
                    'entityId'  => $_article->getId(),
                )
            );
            $this->get('victoire_blog.manager.article')->delete($_article, $bep);
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
    protected function getPageCategoryType()
    {
        return 'victoire_blog_category_type';
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
