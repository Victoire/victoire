<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Builder\ViewReferenceBuilder;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Doctrine\ORM\EntityManagerInterface;
use Victoire\Bundle\CoreBundle\Helper\ViewReferenceHelper;


/**
* BaseReferenceBuilder
*/
abstract class BaseReferenceBuilder
{
    protected $viewReferenceHelper;
    protected $urlBuilder;

    /**
     * @param ViewReferenceHelper $viewReferenceHelper
     * @param UrlBuilder $urlBuilder
     */
    public function __construct(ViewReferenceHelper $viewReferenceHelper, UrlBuilder $urlBuilder) {
        $this->viewReferenceHelper = $viewReferenceHelper;
        $this->urlBuilder = $urlBuilder;
    }

}
