<?php

namespace Victoire\Bundle\SitemapBundle\Domain\Export;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\BusinessPageReference;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * The SitemapExportHandler allow to export sitemap in order
 * to use generated tree with various formats.
 *
 * ref: victoire_sitemap.export.handler.
 */
class SitemapExportHandler
{
    private $entityManager;
    private $pageHelper;
    private $viewReferenceRepo;

    /**
     * SitemapExportHandler constructor.
     *
     * @param EntityManager           $entityManager
     * @param PageHelper              $pageHelper
     * @param ViewReferenceRepository $viewReferenceRepo
     */
    public function __construct(
        EntityManager $entityManager,
        PageHelper $pageHelper,
        ViewReferenceRepository $viewReferenceRepo
    )
    {
        $this->entityManager = $entityManager;
        $this->pageHelper = $pageHelper;
        $this->viewReferenceRepo = $viewReferenceRepo;
    }

    /**
     * Get the whole list of published pages for a given locale.
     *
     * #1 Parse recursively and extract every persisted pages ids
     * #2 load these pages with seo (if exists)
     * #3 parse recursively and extract every VirtualBusinessPages references
     * #4 prepare VirtualBusinessPages.
     *
     * @param $locale
     *
     * @return array
     */
    public function handle($locale)
    {
        $homepage = $this->entityManager->getRepository('VictoirePageBundle:BasePage')
            ->findOneByHomepage($locale);

        /** @var ViewReference $tree */
        $tree = $this->viewReferenceRepo->getOneReferenceByParameters(
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

            return array_unique($ids);
        };

        $pages = $this->entityManager->getRepository('VictoirePageBundle:BasePage')
            ->getAll(true)
            ->joinSeo()
            ->joinSeoTranslations($locale)
            ->filterByIds($getChildrenIds($tree))
            ->run();

        return array_merge($pages, $this->getBusinessPages($tree));
    }

    /**
     * Get all VirtualBusinessPage recursively.
     *
     * @param ViewReference $tree
     * @param array         $businessPages
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getBusinessPages(ViewReference $tree, $businessPages = [])
    {
        foreach ($tree->getChildren() as $child) {
            if ($child instanceof BusinessPageReference
                && $child->getViewNamespace() == 'Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage'
            ) {
                /** @var WebViewInterface $businessPage */
                $businessPage = $this->pageHelper->findPageByReference($child);
                $businessPage->setReference($child);
                $businessPages[] = $businessPage;
            }
            $businessPages = $this->getBusinessPages($child, $businessPages);
        }

        return $businessPages;
    }
}
