<?php

namespace Victoire\Bundle\ViewReferenceBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * BaseReferenceBuilder.
 * ref. victoire_view_reference.base_view_reference.builder
 */
abstract class BaseReferenceBuilder
{
    /**
     * @param View $view
     * @param EntityManager $em
     *
     * @return ViewReference
     */
    abstract public function buildReference(View $view, EntityManager $em);
}
