<?php
namespace Victoire\Bundle\PageBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\PageBundle\Event\Menu\BasePageMenuContextualEvent;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;


/**
 * When dispatched, this listener add items to a KnpMenu
 * TODO implements an interface (what name ?) which force to implements addContextual, addGlobal and getMainItem
 */
class PageMenuListener
{
    private $menuBuilder;


    /**
     * {@inheritDoc}
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * add a contextual menu item
     */
    public function addContextual(BasePageMenuContextualEvent $event)
    {

        $mainItem = $this->getMainItem();
        $mainItem->addChild('menu.page.settings',
            array(
                'route' => 'victoire_core_page_settings',
                'routeParameters' => array('id' => $event->getPage()->getId())
            )
        )->setLinkAttribute('data-toggle', 'vic-modal');
        $mainItem->addChild('menu.page.seoSettings',
            array(
                'route' => 'victoire_seo_pageSeo_settings',
                'routeParameters' => array('id' => $event->getPage()->getId())
            )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        if ($event->getPage()->getTemplate()) {
            $mainItem->addChild('menu.page.template',
                array(
                    'route' => 'victoire_core_template_show',
                    'routeParameters' => array('slug' => $event->getPage()->getTemplate()->getSlug())
                )
            );
        }

        return $mainItem;
    }


    /**
     * add global menu items
     */
    public function addGlobal(Event $event)
    {

        $mainItem = $this->getMainItem();

        $mainItem->addChild('menu.page.new', array(
            'route'     => 'victoire_core_page_new'
            )
        )
        ->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    private function getMainItem()
    {
        if ($menuPage = $this->menuBuilder->getTopNavbar()->getChild('menu.page')) {
            return $menuPage;
        } else {
            return $this->menuBuilder->createDropdownMenuItem(
                $this->menuBuilder->getTopNavbar(),
                "menu.page"
            );
        }
    }
}
