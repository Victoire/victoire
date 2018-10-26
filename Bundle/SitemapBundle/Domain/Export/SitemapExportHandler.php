<?php

namespace Victoire\Bundle\SitemapBundle\Domain\Export;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\PageBundle\Entity\BasePage;
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
    ) {
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
            [
                'viewId' => $homepage->getId(),
                'locale' => $locale,
            ],
            true,
            true
        );

        $ids = [$tree->getViewId()];

        $getChildrenIds = function (ViewReference $tree) use (&$getChildrenIds, $ids) {
            foreach ($tree->getChildren() as $child) {
                if (null !== $child->getViewId()) {
                    $ids[] = $child->getViewId();
                    $ids = array_merge($ids, $getChildrenIds($child));
                }
            }

            return $ids;
        };
        $ids = array_unique($getChildrenIds($tree));

        $pages = $this->entityManager->getRepository(BasePage::class)
            ->getAll(true)
            ->joinSeo()
            ->joinTranslations($locale)
            ->filterByIds($ids)
            ->run();
        /** @var BasePage $page */
        foreach ($pages as $page) {
            $page->setCurrentLocale($locale);
            $this->entityManager->refresh($page);
            $page->translate($locale);
        }

        return array_merge($pages, $this->getBusinessPages($tree));
    }

    public function serialize($pages)
    {
        $sitemapEntries = [];

        /** @var BasePage $page */
        foreach ($pages as $page) {
            // BusinessTemplate have no getUrl() method
            if ($page instanceof BusinessTemplate) {
                continue;
            }

            $seo = $page->getSeo();
            $sitemapEntry = [
                'url'               => $page->getUrl(),
                'sitemapChangeFreq' => $seo === null ? 'monthly' : $seo->getSitemapChangeFreq(),
                'sitemapPriority'   => $seo === null ? 0.5 : $seo->getSitemapPriority(),
            ];

            // This data is optional in sitemap, add it only if a publication date is available
            // see https://www.sitemaps.org/protocol.html#xmlTagDefinitions
            if (null !== $page->getPublishedAt() && $page->getPublishedAt() instanceof \DateTime) {
                $sitemapEntry['publishedAt'] = $page->getPublishedAt()->format('Y-m-d');
            }
            $sitemapEntries[] = $sitemapEntry;
        }

        return json_encode($sitemapEntries);
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
                && $child->getViewNamespace() == VirtualBusinessPage::class
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
