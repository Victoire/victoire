<?php

namespace Victoire\Bundle\BlogBundle\Manager\Interfaces;

use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\CoreBundle\Manager\Interfaces\ReferenceBuilderInterface;

/**
 * List blog managers methods.
 *
 * Interface BlogReferenceBuilderInterface
 */
interface BlogReferenceBuilderInterface extends ReferenceBuilderInterface
{
    /**
     * Build Reference for a view.
     *
     * @param Blog $view
     *
     * @return array
     */
    public function buildReference(Blog $view);
}
