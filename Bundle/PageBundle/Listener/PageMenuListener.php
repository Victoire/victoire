<?php

namespace Victoire\Bundle\PageBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Listener\MenuListenerInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent;

/**
 * When dispatched, this listener add items to a KnpMenu.
 */
class PageMenuListener implements MenuListenerInterface
{
    private $menuBuilder;

    /**
     * Constructor.
     *
     * @param MenuBuilder $menuBuilder
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * add a contextual menu item.
     *
     * @param PageMenuContextualEvent $event
     *
     * @return \Knp\Menu\ItemInterface <\Knp\Menu\ItemInterface, NULL>
     */
    public function addContextual($event)
    {
        //get the current page
        $page = $event->getPage();

        $mainItem = $this->getMainItem();
        $mainItem->addChild('menu.page.settings',
            [
                'route'           => 'victoire_core_page_settings',
                'routeParameters' => ['id' => $page->getId()],
            ]
        )->setLinkAttribute('data-toggle', 'vic-modal');
        $mainItem->addChild('menu.page.seoSettings',
            [
                'route'           => 'victoire_seo_pageSeo_settings',
                'routeParameters' => ['id' => $page->getId()],
            ]
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    /**
     * add global menu items.
     *
     * @param Event $event
     *
     * @return \Knp\Menu\ItemInterface <\Knp\Menu\ItemInterface, NULL>
     */
    public function addGlobal(Event $event)
    {
        $mainItem = $this->getMainItem();

        $mainItem->addChild('menu.page.new', [
            'route'     => 'victoire_core_page_new',
            ]
        )
        ->setExtra('translation_domain', 'victoire')
        ->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    /**
     * Get the main item.
     *
     * @return \Knp\Menu\ItemInterface <\Knp\Menu\ItemInterface, NULL>|\Knp\Menu\ItemInterface
     */
    public function getMainItem()
    {
        $menuPage = $this->menuBuilder->getTopNavbar()->getChild('menu.page');

        if ($menuPage) {
            return $menuPage;
        } else {
            return $this->menuBuilder->createDropdownMenuItem(
                $this->menuBuilder->getTopNavbar(),
                'menu.page'
            );
        }
    }
}
