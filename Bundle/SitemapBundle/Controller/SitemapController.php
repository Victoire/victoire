<?php

namespace Victoire\Bundle\SitemapBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;
use Victoire\Bundle\SitemapBundle\Form\SitemapPriorityPageSeoType;

/**
 * Victoire sitemap controller.
 *
 * @Route("/sitemap")
 */
class SitemapController extends Controller
{
    /**
     * Get Sitemap as XML.
     *
     * @Route(".{_format}", name="victoire_sitemap_xml", Requirements={"_format" = "xml"})
     *
     * @return Response
     */
    public function xmlAction(Request $request)
    {
        $pages = $this->get('victoire_sitemap.export.handler')->handle(
            $request->getLocale()
        );

        return $this->render('VictoireSitemapBundle:Sitemap:sitemap.xml.twig', [
            'pages' => $pages,
        ]);
    }

    /**
     * Show Sitemap as tree and save new order if necessary.
     *
     * @Route("/reorganize", name="victoire_sitemap_reorganize", options={"expose"=true})
     * @Template()
     *
     * @return JsonResponse
     */
    public function reorganizeAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $this->get('victoire_sitemap.sort.handler')->handle(
                $request->request->get('sorted')
            );
            $response['message'] = $this->get('translator')->trans('sitemap.changed.success', [], 'victoire');
        }

        $basePageRepo = $this->getDoctrine()->getManager()->getRepository('VictoirePageBundle:BasePage');
        $basePages = $basePageRepo
            ->getAll(true)
            ->joinSeo()
            ->joinSeoTranslations($request->getLocale())
            ->run();

        $forms = [];
        foreach ($basePages as $_page) {
            $_pageSeo = $_page->getSeo() ?: new PageSeo();
            $forms[$_page->getId()] = $this->createSitemapPriorityType($_page, $_pageSeo)->createView();
        }

        $response['success'] = true;
        $response['html'] = $this->container->get('templating')->render(
            'VictoireSitemapBundle:Sitemap:reorganize.html.twig',
            [
                'pages' => $basePageRepo->findByParent(null, ['position' => 'ASC']),
                'forms' => $forms,
            ]
        );

        return new JsonResponse($response);
    }

    /**
     * Change the sitemap priority for the given page.
     *
     * @Route("/changePriority/{id}", name="victoire_sitemap_changePriority", options={"expose"=true})
     *
     * @return JsonResponse
     */
    public function changePriorityAction(Request $request, BasePage $page)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $pageSeo = $page->getSeo() ?: new PageSeo();
        $pageSeo->setCurrentLocale($request->getLocale());

        $form = $this->createSitemapPriorityType($page, $pageSeo);
        $form->handleRequest($request);
        $params = [
            'success' => $form->isValid(),
        ];

        if ($form->isValid()) {
            $page->setSeo($pageSeo);
            $em->persist($pageSeo);
            $em->flush();
        }

        return new JsonResponse($params);
    }

    /**
     * Create a sitemap priority type.
     *
     * @param BasePage $page
     * @param PageSeo  $pageSeo
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createSitemapPriorityType(BasePage $page, PageSeo $pageSeo)
    {
        $form = $this->createForm(SitemapPriorityPageSeoType::class, $pageSeo, [
                'action' => $this->generateUrl('victoire_sitemap_changePriority', [
                        'id' => $page->getId(),
                    ]
                ),
                'method' => 'PUT',
                'attr'   => [
                    'class'       => 'sitemapPriorityForm form-inline',
                    'data-pageId' => $page->getId(),
                    'id'          => 'sitemap-priority-type-'.$page->getId(),
                    'style'       => 'display: inline;',
                ],
            ]
        );

        return $form;
    }
}
