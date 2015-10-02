<?php

namespace Victoire\Bundle\BusinessPageBundle\Manager\Interfaces;

use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Manager\Interfaces\ReferenceBuilderInterface;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * List page managers methods.
 *
 * Interface PageReferenceBuilderInterface
 */
interface VirtualBusinessPageReferenceBuilderInterface extends ReferenceBuilderInterface
{
    /**
     * Build Reference for a view.
     *
     * @param VirtualBusinessPage $view
     *
     * @return array
     */
    public function buildReference(VirtualBusinessPage $view);
}
