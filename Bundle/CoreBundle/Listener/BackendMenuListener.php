<?php

namespace Victoire\Bundle\CoreBundle\Listener;


use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;

class BackendMenuListener implements MenuListenerInterface
{
    private $menuBuilder;

    /**
     * Constructor
     * @param MenuBuilder $menuBuilder
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * add a contextual menu item
     * @param Event $event
     *
     * @return Ambigous <\Knp\Menu\ItemInterface, NULL>
     */
    public function addContextual($event)
    {
        return null;
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
        $this->mainItem = $this->menuBuilder->getLeftNavbar();

        $this->mainItem->addChild('hamburger_menu.backend.back_home', array(
                'route' => 'victoire_core_page_homepage',
            )
        );

        return $this->mainItem;
    }
}