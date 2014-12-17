<?php

namespace Victoire\Bundle\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * article Controller
 *
 * @Route("/victoire-dcms/article")
 */
class ArticleController extends Controller
{
    /**
     * Create article
     * @Route("/create", name="victoire_blog_article_create")
     *
     * @return template
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
            $entityManager->flush();

            if (null !== $this->container->get('victoire_core.helper.business_entity_helper')->findByEntityInstance($article)) {
                $article = $this->container
                     ->get('victoire_business_entity_page.business_entity_page_helper')
                     ->generateEntityPageFromPattern($article->getPattern(), $article);
            }

            return new JsonResponse(array(
                "success"  => true,
                "url"      => $this->generateUrl('victoire_core_page_show', array('url' => $article->getUrl()))
            ));
        } else {
            $formErrorHelper = $this->container->get('victoire_form.error_helper');

            return new JsonResponse(
                array(
                    "success" => false,
                    "message" => $formErrorHelper->getRecursiveReadableErrors($form),
                    'html'    => $this->container->get('victoire_templating')->render(
                        'VictoireBlogBundle:Article:new.html.twig',
                        array(
                            'form' => $form->createView()
                        )
                    )
                )
            );
        }
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
        $form = $this->createForm('victoire_article_type', $article);

        return new JsonResponse(
            array(
                'html' => $this->container->get('victoire_templating')->render(
                    'VictoireBlogBundle:Article:new.html.twig',
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

        $pattern = $article->getPattern();

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
    protected function getNewPageType()
    {
        return 'victoire_article_type';
    }

    /**
     *
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return "VictoireBlogBundle:Article";
    }
}
