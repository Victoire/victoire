<?php
namespace Victoire\Bundle\PageBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Victoire\Bundle\CoreBundle\Form\PageType;
use Victoire\Bundle\CoreBundle\Form\TemplateType;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * undocumented class
 *
 **/
class BasePageController extends Controller
{

    /**
     * @param $page
     * @return template
     *
     */
    public function deleteAction(BasePage $page)
    {
        if (!$page->isUndeletable()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->remove($page);
            $em->flush();

            $pageRepo = $this->getDoctrine()->getManager()->getRepository('VictoirePageBundle:BasePage');
            $homepage = $pageRepo->findOneByHomepage(true);

            return array(
                'success' => true,
                'url'     => $this->generateUrl('victoire_cms_page_show', array('url'     => $homepage->getUrl()))
            );

        } else {
            return array(
                'success' => false,
                'message' => $this->get('translator')->trans('page.undeletable', array(), 'victoire')
            );

        }

    }

    /**
     * @param url $url
     * @return Template
     *
     */
    public function showAction($url)
    {
        //the response
        $response = null;

        //manager
        $manager = $this->getDoctrine()->getManager();
        $basePageRepository = $manager->getRepository('VictoirePageBundle:BasePage');
        $routeRepository = $manager->getRepository('VictoireCoreBundle:Route');

        //get the page
        $page = $basePageRepository->findOneByUrl($url);

        //no page found using the url, we look for previous url
        if ($page === null) {
            $route = $routeRepository->findOneMostRecentByUrl($url);
            if ($route !== null) {
                //the page linked to the old url
                $page = $route->getPage();

                //the current url
                $url = $page->getUrl();

                //get the base url
                $router = $this->container->get('router');
                $context = $router->getContext();
                //the host
                $host = $context->getHost();
                //the scheme
                $scheme = $context->getScheme();

                //get the complete url
                $completeUrl = $scheme.'://'.$host.'/'.$url;

                //redirect to the current url
                $response = $this->redirect($completeUrl);
            }
        } else {
            if ($page->getSeo() && $page->getSeo()->getRedirectTo() && !$this->get('session')->get('victoire.edit_mode', false)) {
                //a redirection is required by the seo
                $seoUrl = $page->getSeo()->getRedirectTo()->getUrl();

                //generate the url
                $url = $this->generateUrl('victoire_cms_page_show', array('url' => $seoUrl));
                //generate the redirect
                $response = $this->redirect($url);
            } else {
                //add the page to twig
                $this->get('twig')->addGlobal('page', $page);

                $event = new \Victoire\Bundle\CoreBundle\Event\Menu\BasePageMenuContextualEvent($page);
                $this->get('event_dispatcher')->dispatch('victoire_cms.' . $page->getType() . '_menu.contextual', $event); //TODO : il serait bon de faire des constantes pour les noms d'évents

                $response = $this->container->get('victoire_templating')->renderResponse(
                    'AppBundle:Layout:' . $page->getLayout() . '.html.twig',
                    array('page' => $page, 'id' => $page->getId())
                );
            }
        }

        //throw an exception is the page is not valid
        $this->isPageValid($page);

        return $response;
    }


    /**
     * New page
     *
     * @return template
     */
    protected function newAction($isHomepage = false)
    {

        $em = $this->getDoctrine()->getManager();
        $page = $this->getNewPage();
        $page->setHomepage($isHomepage ? $isHomepage : 0);
        $form = $this->container->get('form.factory')->create($this->getNewPageType(), $page);

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            if ($page->getParent()) {
                $pageNb = count($page->getParent()->getChildren());
            } else {
                $pageNb = count($em->getRepository('VictoirePageBundle:Page')->findByParent(null));
            }
            // + 1 because position start at 1, not 0
            $page->setPosition($pageNb + 1);

            if ($template = $page->getTemplate()) {
                $page->setWidgetMap($template->getWidgetMap());
            }

            $page->setAuthor($this->getUser());
            $em->persist($page);
            $em->flush();

            return array(
                "success"  => true,
                "url"      => $this->generateUrl('victoire_cms_page_show', array('url' => $page->getUrl()))
            );
        }

        return array(
            "success" => false,
            'html'    => $this->container->get('victoire_templating')->render(
                $this->getBaseTemplatePath() . ':new.html.twig',
                array('form' => $form->createView())
            )
        );
    }

    /**
     * Page settings
     *
     * @param page $page
     * @return template
     */
    protected function settingsAction(BasePage $page)
    {
        $em = $this->getDoctrine()->getManager();

        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->create($this->getPageSettingsType(), $page);

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $em->persist($page);
            $em->flush();

            return array(
                'success' => true,
                "url"     => $this->generateUrl('victoire_cms_page_show', array('url' => $page->getUrl()))
            );

        }

        return array(
            'success' => false,
            'html' => $this->container->get('victoire_templating')->render(
                $this->getBaseTemplatePath() . ':settings.html.twig',
                array(
                    'page' => $page,
                    'form' => $form->createView()
                )
            )
        );
    }

    /**
     * If the valid is not valid, an exception is thrown
     *
     * @param Page $page
     * @throws NotFoundHttpException
     */
    protected function isPageValid($page)
    {
        $errorMessgae = 'The page was not found.';

        //there is no page
        if ($page === null) {
            throw new NotFoundHttpException($errorMessgae);
        }

        $isPublished = $page->isPublished();
        $isPageOwner = $this->get('security.context')->isGranted('PAGE_OWNER', $page);
        $granted = $this->get('security.context')->isGranted('PAGE_OWNER', $page);

        //a page not published, not owned, nor granted throw an exception
        if (!$isPublished && !$isPageOwner && !$granted) {
            throw new NotFoundHttpException($errorMessgae);
        }
    }
}
