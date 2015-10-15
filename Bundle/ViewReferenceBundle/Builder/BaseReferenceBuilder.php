<?php

namespace Victoire\Bundle\ViewReferenceBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;

/**
 * BaseReferenceBuilder.
 * ref. victoire_view_reference.base_view_reference.builder
 */
abstract class BaseReferenceBuilder
{
    protected $viewReferenceHelper;
    protected $urlBuilder;

    /**
     * @param ViewReferenceHelper $viewReferenceHelper
     * @param UrlBuilder          $urlBuilder
     */
    public function __construct(ViewReferenceHelper $viewReferenceHelper, UrlBuilder $urlBuilder)
    {
        $this->viewReferenceHelper = $viewReferenceHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param View $view
     * @param EntityManager $em
     *
     * @return array
     */
    abstract public function buildReference(View $view, EntityManager $em);
}
