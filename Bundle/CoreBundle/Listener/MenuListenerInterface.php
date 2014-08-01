<?php

namespace Victoire\Bundle\CoreBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent;

/**
 * This class add items in admin menu
 **/
interface MenuListenerInterface
{

    /**
     * Constructor
     *
     * @param MenuBuilder $menuBuilder
     */
    public function __construct(MenuBuilder $menuBuilder);

    /**
     * add a contextual menu item
     *
     * @param PageMenuContextualEvent $event
     *
     * @return Ambigous <\Knp\Menu\ItemInterface, NULL>
     */
    public function addContextual(PageMenuContextualEvent $event);

    /**
     * add global menu items
     *
     * @param Event $event
     *
     * @return Ambigous <\Knp\Menu\ItemInterface, NULL>
     */
    public function addGlobal(Event $event);

}
