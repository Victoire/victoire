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
     * Get the whole list of published pages
     * #1 get the _locale related homepage
     * #2 parse recursively and extract every persisted pages ids
     * #3 load these pages with seo (if exists)
     * #4 parse recursively and extract every VirtualBusinessPages references
     * #5 prepare VirtualBusinessPages.
     *
     * @Route(".{_format}", name="victoire_sitemap_xml", Requirements={"_format" = "xml"})
     *
     * @return Response
     */
    public function xmlAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $homepage = $em->getRepository('VictoirePageBundle:BasePage')
            ->findOneByHomepage($request->getLocale());

        /** @var ViewReference $tree */
        $tree = $this->get('victoire_view_reference.repository')->getOneReferenceByParameters(
            ['viewId' => $homepage->getId()],
            true,
            true
        );

        $ids = [$tree->getViewId()];

        $getChildrenIds = function (ViewReference $tree) use (&$getChildrenIds, $ids) {
            foreach ($tree->getChildren() as $child) {
                $ids[] = $child->getViewId();
                $ids = array_merge($ids, $getChildrenIds($child));
            }

            return $ids;
        };

        $pages = $em->getRepository('VictoirePageBundle:BasePage')
            ->getAll(true)
            ->joinSeo()
            ->filterByIds($getChildrenIds($tree))
            ->run();

        /** @var PageHelper $pageHelper */
        $pageHelper = $this->get('victoire_page.page_helper');
        $entityManager = $this->getDoctrine()->getManager();

        $getBusinessPages = function (ViewReference $tree) use (&$getBusinessPages, $pageHelper, $entityManager) {
            $businessPages = [];
            foreach ($tree->getChildren() as $child) {
                if ($child instanceof BusinessPageReference
                    && $child->getViewNamespace() == 'Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage') {
                    $entity = $entityManager->getRepository($child->getEntityNamespace())->find($child->getEntityId());
                    /** @var WebViewInterface $businessPage */
                    $businessPage = $pageHelper->findPageByReference($child, $entity);
                    $businessPage->setReference($child);
                    $businessPages[] = $businessPage;
                }
                $businessPages = array_merge($businessPages, $getBusinessPages($child));
            }

            return $businessPages;
        };

        $pages = array_merge($pages, $getBusinessPages($tree));

        return $this->render('VictoireSitemapBundle:Sitemap:sitemap.xml.twig', [
            'pages' => $pages,
        ]);
    }

    /**
     * Show sitemap as tree and reorganize it by dnd.
     *
     * @Route("/reorganize", name="victoire_sitemap_reorganize", options={"expose"=true})
     * @Template()
     *
     * @return JsonResponse
     */
    public function reorganizeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $pageRepo = $em->getRepository('VictoirePageBundle:BasePage');
        $response = [
            'success' => false,
        ];
        if ($request->getMethod() === 'POST') {
            $sorted = $request->request->get('sorted');
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
        $response['html'] = $this->container->get('templating')->render(
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
