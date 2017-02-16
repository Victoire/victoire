<?php

namespace Victoire\Bundle\BusinessPageBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Listener\MenuListenerInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent;

/**
 * When dispatched, this listener add items to a KnpMenu.
 */
class BusinessPageMenuListener implements MenuListenerInterface
{
    protected $menuBuilder = null;

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
     * Add a global menu item.
     *
     * @param Event $event
     *
     * @return \Knp\Menu\ItemInterface
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function addGlobal(Event $event)
    {
        $this->mainItem = $this->menuBuilder->getBottomLeftNavbar()->getChild('menu.additionals');

        if ($this->menuBuilder->isGranted('ROLE_VICTOIRE_BET')) {
            $this
                ->mainItem
                ->addChild(
                    'menu.business_template',
                    [
                        'route' => 'victoire_business_template_index',
                        'linkAttributes' => [
                            'class' => 'v-drop__anchor',
                        ],
                    ]
                )
                ->setLinkAttribute('data-toggle', 'vic-modal');
        }

        return $this->mainItem;
    }

    /**
     * Add the parent menu for a page that extends another one.
     *
     * @param PageMenuContextualEvent $event
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function addContextual($event)
    {

        $bottomRightNavbar = $this->menuBuilder->getBottomRightNavbar();

        //if there is a template, we add the link in the top bar
        $bottomRightNavbar->addChild('menu.page.settings',
            [
                'route'           => 'victoire_business_template_edit',
                'routeParameters' => ['id' => $event->getPage()->getId()],
                'linkAttributes' => [
                    'class' => 'v-btn v-btn--sm v-btn--transparent',
                ],
            ]
        )->setLinkAttribute('data-toggle', 'vic-modal');
        $bottomRightNavbar->addChild('menu.page.seoSettings',
            [
                'route'           => 'victoire_seo_pageSeo_settings',
                'routeParameters' => ['id' => $event->getPage()->getId()],
                'linkAttributes' => [
                    'class' => 'v-btn v-btn--sm v-btn--transparent',
                ],
            ]
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return;
    }
}
