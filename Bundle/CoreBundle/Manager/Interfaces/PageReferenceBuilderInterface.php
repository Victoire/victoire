<?php

namespace Victoire\Bundle\CoreBundle\Manager\Interfaces;

use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * List page managers methods.
 *
 * Interface PageReferenceBuilderInterface
 */
interface PageReferenceBuilderInterface extends ReferenceBuilderInterface
{
    /**
     * Build Reference for a view.
     *
     * @param Page $view
     *
     * @return array
     */
    public function buildReference(Page $view);
}
