<?php

namespace Victoire\Bundle\CoreBundle\Listener;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Builder\ViewCssBuilder;
use Victoire\Bundle\CoreBundle\Event\PageRenderEvent;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;

class ViewCssListener
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
     * Generate cssHash and css file for current View if cssHash has not been set yet or is not up to date.
     *
     * @param PageRenderEvent $event
     *
     * @throws \Exception
     */
    public function onRenderPage(PageRenderEvent $event)
    {
        $currentView = $event->getCurrentView();

        if ($currentView instanceof VirtualBusinessPage) {
            $currentView->setCssHash($currentView->getTemplate()->getCssHash());
        } elseif (!$currentView->getCssHash() || !$currentView->isCssUpToDate()) {

            //Get View's widgets
            $widgetRepo = $this->entityManager->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget');
            $this->widgetMapBuilder->build($currentView);
            $widgets = $widgetRepo->findAllWidgetsForView($currentView);

            //Generate CSS file and set View's CSS as up to date
            $oldHash = $currentView->getCssHash();
            $currentView->changeCssHash();
            $this->viewCssBuilder->updateViewCss($oldHash, $currentView, $widgets);
            $currentView->setCssUpToDate(true);
            $this->entityManager->persist($currentView);
            $this->entityManager->flush($currentView);
        }
    }
}
