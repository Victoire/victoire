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
     * Generate cssHash and css file for current View if cssHash has not been set yet.
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
        } elseif (!$viewHash = $currentView->getCssHash()) {
            $currentView->changeCssHash();
            $this->entityManager->persist($currentView);
            $this->entityManager->flush($currentView);

            $widgetRepo = $this->entityManager->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget');

            $this->widgetMapBuilder->build($currentView);
            $widgets = $widgetRepo->findAllWidgetsForView($currentView);
            $this->viewCssBuilder->generateViewCss($currentView, $widgets);
        }
    }
}
