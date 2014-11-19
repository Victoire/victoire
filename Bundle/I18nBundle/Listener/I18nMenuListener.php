<?php

namespace Victoire\Bundle\I18nBundle\Listener;

use Victoire\Bundle\PageBundle\Listener\PageMenuListener;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;

class I18nMenuListener extends PageMenuListener
{

	/**
     * Constructor
     *
     * @param MenuBuilder $menuBuilder
     */
	public function __construct(MenuBuilder $menuBuilder) 
	{
        parent::__construct($menuBuilder);
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
        $mainItem->addChild('menu.page.i18n.addTranslation',
            array(
                'route' => 'victoire_i18n_page_translation',
                'routeParameters' => array('pageId' => $page->getId())
            )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }
}