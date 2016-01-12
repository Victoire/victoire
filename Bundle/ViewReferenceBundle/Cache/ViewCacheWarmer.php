<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Helper\ViewHelper;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceManager;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;

/**
 * Called (for example on kernel request) to create the viewsReference cache file
 * ref. victoire_view_reference.cache_warmer.
 */
class ViewCacheWarmer
{
    private $viewHelper;
    private $viewReferenceRepository;
    private $viewReferenceManager;
    private $entityManager;

    /**
     * ViewCacheWarmer constructor.
     * @param ViewHelper $viewHelper
     * @param ViewReferenceRepository $viewReferenceRepository
     * @param ViewReferenceManager $viewReferenceManager
     * @param EntityManager $entityManager
     */
    public function __construct(
        ViewHelper $viewHelper,
        ViewReferenceRepository $viewReferenceRepository,
        ViewReferenceManager $viewReferenceManager,
        EntityManager $entityManager
    ) {
        $this->viewHelper = $viewHelper;
        $this->viewReferenceRepository= $viewReferenceRepository;
        $this->viewReferenceManager= $viewReferenceManager;
        $this->entityManager = $entityManager;
    }

    /**
     * Warm the view cache file (if needed or force mode).
     *
     * @param string $cacheDir Where does the viewsReferences file should take place
     */
    public function warmUp($cacheDir)
    {
        if (!$this->viewReferenceRepository->hasReference()) {
            $this->viewReferenceManager->saveReferences(
                $this->viewHelper->buildViewsReferences()
            );
        }
    }
}
