<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Doctrine\ORM\EntityManagerInterface;

/**
* BaseReferenceBuilder
*/
abstract class BaseReferenceBuilder
{
    protected $viewCacheHelper;
    protected $em;
    protected $urlBuilder;

    public function __construct(ViewCacheHelper $viewCacheHelper, EntityManager $em, UrlBuilder $urlBuilder) {
        $this->viewCacheHelper = $viewCacheHelper;
        $this->em = $em;
        $this->urlBuilder = $urlBuilder;
    }
    /**
     * @return mixed
     */
    public function getUrlBuilder()
    {
        return $this->urlBuilder;
    }

    /**
     * @param UrlBuilder $urlBuilder
     */
    public function setUrlBuilder(UrlBuilder $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }


    public function setViewCacheHelper(
        ViewCacheHelper $viewCacheHelper
        ) {
        $this->viewCacheHelper = $viewCacheHelper;

        return $this;
    }

    /**
     * @return ViewCacheHelper
     */
    protected function getViewCacheHelper()
    {
        return $this->viewCacheHelper;
    }

    public function setEntityManager(
        EntityManagerInterface $entityManager
        ) {
        $this->em = $entityManager;

        return $this;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->em;
    }
}
