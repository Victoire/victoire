<?php

namespace Victoire\Bundle\SitemapBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;

/**
 * Victoire sitemap controller.
 *
 * @Route("/sitemap")
 */
class SitemapController extends Controller
{
    /**
     * Change the sitemap priority for the given page.
     *
     * @Route(".{_format}", name="victoire_sitemap_xml", Requirements={"_format" = "xml"})
     * @Template("VictoireSitemapBundle:Sitemap:sitemap.xml.twig")
     *
     * @return template
     */
    public function xmlAction()
    {
        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('VictoirePageBundle:BasePage')
            ->getAll()
            ->run();

        $indexedPages = [];
        foreach ($pages as $page) {
            if (!$page->getSeo() || $page->getSeo()->isSitemapIndexed()) {
                $indexedPages[] = $page;
            }
        }

        return [
            'pages' => $indexedPages,
        ];
    }

    /**
     * Show sitemap as tree and reorganize it by dnd.
     *
     * @Route("/reorganize", name="victoire_sitemap_reorganize", options={"expose"=true})
     * @Template()
     *
     * @return JsonResponse
     */
    public function reorganizeAction()
    {
        $em = $this->getDoctrine()->getManager();
        $pageRepo = $em->getRepository('VictoirePageBundle:BasePage');
        $response = [
            'success' => false,
        ];
        if ($this->get('request')->getMethod() === 'POST') {
            $sorted = $this->getRequest()->request->get('sorted');
            $depths = [];
            //reorder pages positions
            foreach ($sorted as $item) {
                $depths[$item['depth']][$item['item_id']] = 1;
                $page = $pageRepo->findOneById($item['item_id']);
                if ($page !== null) {
                    if ($item['parent_id'] !== '') {
                        $parent = $pageRepo->findOneById($item['parent_id']);
                        $page->setParent($parent);
                    } else {
                        $page->setParent(null);
                    }
                    $page->setPosition(count($depths[$item['depth']]));
                    $em->persist($page);
                }
            }
            $em->flush();

            $response = [
                'success' => true,
                'message' => $this->get('translator')->trans('sitemap.changed.success', [], 'victoire'),
            ];
        }

        $allPages = $em->getRepository('VictoirePageBundle:BasePage')->findAll();
        $forms = [];
        foreach ($allPages as $_page) {
            $forms[$_page->getId()] = $this->createSitemapPriorityType($_page)->createView();
        }

        $pages = $em->getRepository('VictoirePageBundle:BasePage')->findByParent(null, ['position' => 'ASC']);
        $response['html'] = $this->container->get('victoire_templating')->render(
            'VictoireSitemapBundle:Sitemap:reorganize.html.twig',
            [
                'pages' => $pages,
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
        $form = $this->createForm(
            'victoire_sitemap_priority_pageseo_type',
            $page->getSeo(),
            [
                'action'     => $this->generateUrl('victoire_sitemap_changePriority', [
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
