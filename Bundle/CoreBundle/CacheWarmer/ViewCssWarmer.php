<?php

namespace Victoire\Bundle\CoreBundle\CacheWarmer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Victoire\Bundle\CoreBundle\Builder\ViewCssBuilder;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;

/**
 * Called (for example on kernel request) to create the widgets.css file
 * ref. victoire_core.cache_warmer.view_css_warmer.
 */
class ViewCssWarmer extends CacheWarmer
{
    private $viewCssBuilder;
    private $entityManager;
    private $widgetMapBuilder;

    /**
     * Construct.
     *
     * @param ViewCssBuilder $viewCssBuilder
     */
    public function __construct(ViewCssBuilder $viewCssBuilder, EntityManager $entityManager, WidgetMapBuilder $widgetMapBuilder)
    {
        $this->viewCssBuilder = $viewCssBuilder;
        $this->entityManager = $entityManager;
        $this->widgetMapBuilder = $widgetMapBuilder;
    }

    /**
     * Iterate on all views and launch css generation for each view.
     *
     * @param string $cacheDir
     */
    public function warmUp($cacheDir)
    {
        if (!$this->entityManager->getConnection()->isConnected()) {
            return;
        }
        $viewRepo = $this->entityManager->getRepository('Victoire\Bundle\CoreBundle\Entity\View');
        $widgetRepo = $this->entityManager->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget');
        $views = $viewRepo->findAll();

        $this->viewCssBuilder->clearViewCssFolder();

        /* @var $views View[] */
        foreach ($views as $view) {
            if (!$viewHash = $view->getCssHash()) {
                $view->changeCssHash();
                $this->entityManager->persist($view);
                $this->entityManager->flush($view);
            }

            $this->widgetMapBuilder->build($view);
            $widgets = $widgetRepo->findAllWidgetsForView($view);
            $this->viewCssBuilder->generateViewCss($view, $widgets);
        }
    }

    /**
     * Is the warmer optionnal.
     *
     * @return bool
     */
    public function isOptional()
    {
        return false;
    }
}
