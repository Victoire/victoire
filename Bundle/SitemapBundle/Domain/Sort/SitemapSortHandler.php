<?php

namespace Victoire\Bundle\SitemapBundle\Domain\Sort;

use Doctrine\ORM\EntityManager;

/**
 * The SitemapSortHandler allow to reorganize Sitemap order.
 *
 * ref: victoire_sitemap.sort.handler.
 */
class SitemapSortHandler
{
    private $entityManager;

    /**
     * SitemapSortHandler constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Reorder pages positions.
     *
     * @param array $sorted The sorted array from Nested Sortable
     */
    public function handle(array $sorted)
    {
        $pageRepo = $this->entityManager->getRepository('VictoirePageBundle:BasePage');

        $depths = [];
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
                $this->entityManager->persist($page);
            }
        }
        $this->entityManager->flush();
    }
}