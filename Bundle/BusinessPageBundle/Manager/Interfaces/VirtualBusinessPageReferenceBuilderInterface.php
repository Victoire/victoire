<?php

namespace Victoire\Bundle\BusinessPageBundle\Manager\Interfaces;

use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\CoreBundle\Manager\Interfaces\ReferenceBuilderInterface;

/**
 * List page managers methods
 *
 * Interface PageReferenceBuilderInterface
 * @package Victoire\Bundle\CoreBundle\Manager
 */
interface VirtualBusinessPageReferenceBuilderInterface extends ReferenceBuilderInterface
{
    /**
     * Build Reference for a view
     *
     * @param VirtualBusinessPage $view
     * @return array
     */
    public function buildReference(VirtualBusinessPage $view);
}
