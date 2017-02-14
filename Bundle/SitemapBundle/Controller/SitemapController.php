<?php

namespace Victoire\Bundle\SitemapBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;
use Victoire\Bundle\SitemapBundle\Form\SitemapPriorityPageSeoType;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\BusinessPageReference;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

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
        if ($request->getMethod() === 'POST') {
            $this->get('victoire_sitemap.sort.handler')->handle(
                $request->request->get('sorted')
            );
            $response['message'] = $this->get('translator')->trans('sitemap.changed.success', [], 'victoire');
        }

        $basePageRepo = $this->getDoctrine()->getManager()->getRepository('VictoirePageBundle:BasePage');

        $forms = [];
        foreach ($basePageRepo->findAll() as $_page) {
            $forms[$_page->getId()] = $this->createSitemapPriorityType($_page)->createView();
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
        $form = $this->createSitemapPriorityType($page);
        $form->handleRequest($request);
        $params = [
            'success' => $form->isValid(),
        ];
        if ($form->isValid()) {
            if (!$page->getSeo()) {
                $seo = new PageSeo();
                $page->setSeo($seo);
            }
            $this->get('doctrine.orm.entity_manager')->persist($page->getSeo());
            $this->get('doctrine.orm.entity_manager')->flush();

            // congratulate user, the action succeed
            $message = $this->get('translator')->trans(
                'sitemap.changedPriority.success',
                [
                    '%priority%' => $page->getSeo()->getSitemapPriority(),
                    '%pageName%' => $page->getName(),
                    ],
                'victoire'
            );
            $params['message'] = $message;
        }

        return new JsonResponse($params);
    }

    /**
     * Create a sitemap priority type.
     *
     * @return \Symfony\Component\Form\Form The form
     **/
    protected function createSitemapPriorityType(BasePage $page)
    {
        $form = $this->createForm(SitemapPriorityPageSeoType::class, $page->getSeo(), [
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
