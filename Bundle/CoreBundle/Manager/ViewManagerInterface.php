<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Victoire\Bundle\CoreBundle\Entity\View;

interface ViewManagerInterface
{
    /**
     * Build reference for a view
     *
     */
    public function buildReference(View $view);
}