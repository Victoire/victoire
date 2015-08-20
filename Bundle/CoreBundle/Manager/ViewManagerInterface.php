<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * List view managers methods
 *
 * Interface ViewManagerInterface
 * @package Victoire\Bundle\CoreBundle\Manager
 */
interface ViewManagerInterface
{
    /**
     * Build Reference for a view
     *
     * @param View $view
     * @return ViewReference
     */
    public function buildReference(View $view);
}