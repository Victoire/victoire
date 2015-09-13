<?php

namespace Victoire\Bundle\BusinessPageBundle\Manager\Interfaces;

use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\CoreBundle\Manager\Interfaces\ReferenceBuilderInterface;

/**
 * List page managers methods
 *
 * Interface BusinessTemplateReferenceBuilderInterface
 * @package Victoire\Bundle\CoreBundle\Manager
 */
interface BusinessTemplateReferenceBuilderInterface extends ReferenceBuilderInterface
{
    /**
     * Build Reference for a view
     *
     * @param BusinessPage $view
     * @return array
     */
    public function buildReference(BusinessTemplate $view);
}
