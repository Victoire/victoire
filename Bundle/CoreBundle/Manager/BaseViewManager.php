<?php

namespace Victoire\Bundle\CoreBundle\Manager;

use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Doctrine\ORM\EntityManagerInterface;

/**
* BaseViewManager
*/
abstract class BaseViewManager
{
    protected $viewCacheHelper;
    protected $em;

    public function setViewCacheHelper(
        ViewCacheHelper $viewCacheHelper
        ) {
        $this->viewCacheHelper = $viewCacheHelper;

        return $this;
    }

    public function setEntityManager(
        EntityManagerInterface $entityManager
        ) {
        $this->em = $entityManager;

        return $this;
    }
}