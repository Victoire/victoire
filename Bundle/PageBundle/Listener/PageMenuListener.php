<?php
namespace Victoire\Bundle\PageBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Listener\MenuListenerInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent;

/**
 * When dispatched, this listener add items to a KnpMenu
 */
class PageMenuListener implements MenuListenerInterface
{
    private $menuBuilder;

    /**
     * Constructor
     *
     * @param MenuBuilder $menuBuilder
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * add a contextual menu item
     *
     * @param PageMenuContextualEvent $event
     *
     * @return Ambigous <\Knp\Menu\ItemInterface, NULL>
     */
    public function addContextual($event)
    {
        //get the current page
        $page = $event->getPage();

        $mainItem = $this->getMainItem();
        $mainItem->addChild('menu.page.settings',
            array(
                'route' => 'victoire_core_page_settings',
                'routeParameters' => array('id' => $page->getId())
            )
        )->setLinkAttribute('data-toggle', 'vic-modal');
        $mainItem->addChild('menu.page.seoSettings',
            array(
                'route' => 'victoire_seo_pageSeo_settings',
                'routeParameters' => array('id' => $page->getId())
            )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    /**
     * add global menu items
     *
     * @param Event $event
     *
     * @return Ambigous <\Knp\Menu\ItemInterface, NULL>
     */
    public function addGlobal(Event $event)
    {
        $mainItem = $this->getMainItem();

        $mainItem->addChild('menu.page.new', array(
            'route'     => 'victoire_core_page_new'
            )
        )
        ->setExtra('translation_domain', 'victoire')
        ->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    /**
     * Get the main item
     *
     * @return Ambigous <\Knp\Menu\ItemInterface, NULL>|\Knp\Menu\ItemInterface
     */
    public function getMainItem()
    {
        $menuPage = $this->menuBuilder->getTopNavbar()->getChild('menu.page');

        if ($menuPage) {
            return $menuPage;
        } else {
            return $this->menuBuilder->createDropdownMenuItem(
                $this->menuBuilder->getTopNavbar(),
                "menu.page"
            );
        }
    }
}
