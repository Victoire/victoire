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
            //Auto creation of the BEP
            $page = $this->container->get('victoire_business_entity_page.business_entity_page_helper')
                                ->generateEntityPageFromPattern($article->getPattern(), $article);
            $entityManager->persist($page);
            $entityManager->flush();

            return new JsonResponse(array(
                "success"  => true,
                "url"      => $this->generateUrl('victoire_core_page_show', array('url' => $page->getUrl()))
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
     * @param Article $article
     *
     * @Route("/{id}/settings", name="victoire_blog_article_settings")
     *
     * @ParamConverter("article", class="VictoireBlogBundle:Article")
     * @return template
     */
    public function settingsAction(Request $request, Article $article)
    {
        $form = $this->createForm('victoire_article_settings_type', $article);
        $businessProperties = array();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('doctrine.orm.entity_manager')->persist($article);
            $this->get('doctrine.orm.entity_manager')->flush();

            $pattern = $article->getPattern();

            $page = $this->container->get('victoire_page.page_helper')->findPageByParameters(array(
                'viewId' => $pattern->getId(),
                'locale' => $request->getSession()->get('victoire_locale'),
                'entityId' => $article->getId()
            ));

            $response = array(
                'success' => true,
                'url'     => $this->generateUrl(
                    'victoire_core_page_show',
                    array('url' => $page->getUrl())
                )
            );
        } else {
            $response = array(
                'success' => false,
                'html'    => $this->container->get('victoire_templating')->render(
                    'VictoireBlogBundle:Article:settings.html.twig',
                    array(
                        'article'            => $article,
                        'form'               => $form->createView(),
                        'businessProperties' => $businessProperties
                    )
                )
            );
        }

        return new JsonResponse($response);
    }

    /**
     * Page delete
     *
     * @param Article $article
     *
     * @return template
     * @Route("/{id}/delete", name="victoire_core_article_delete")
     * @Template()
     * @ParamConverter("article", class="VictoireBlogBundle:Article")
     */
    public function deleteAction(Article $article)
    {
        try {
            //the entity manager
            $entityManager = $this->get('doctrine.orm.entity_manager');

            //remove the page (soft deletete)
            $article->setVisibleOnFront(false);
            $entityManager->flush($article);
            $entityManager->remove($article);

            //flush the modifications
            $entityManager->flush();

            //redirect to the homepage
            $homepageUrl = $this->generateUrl('victoire_core_page_homepage');

            $response = array(
                'success' => true,
                'url'     => $homepageUrl
            );
        } catch (\Exception $ex) {
            $response = array(
                'success' => false,
                'message' => $ex->getMessage()
            );
        }

        return new JsonResponse($response);
    }
}
