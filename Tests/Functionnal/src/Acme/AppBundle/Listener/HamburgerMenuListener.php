<?php

namespace Acme\AppBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Listener\MenuListenerInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;

/**
 * When dispatched, this listener add items to the victoire hamburger menu.
 */
class HamburgerMenuListener implements MenuListenerInterface
{
    private $menuBuilder;
    private $mainItem;

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
     * add global menu items.
     *
     * @param Event $event
     *
     * @return \Knp\Menu\ItemInterface
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function addGlobal(Event $event)
    {
        $this->mainItem = $this->menuBuilder->getLeftNavbar();

        $this->mainItem->addChild('hamburger_menu.jedi', [
                'route' => 'acme_app_jedi_index',
            ]
        );

        $this->mainItem->addChild('hamburger_menu.spaceship', [
                'route' => 'acme_app_spaceship_index',
            ]
        );

        return $this->mainItem;
    }

    /**
     * No contextual item to inject.
     *
     * @param Event $event
     *
     * @return void
     */
    public function addContextual($event)
    {
    }
}
