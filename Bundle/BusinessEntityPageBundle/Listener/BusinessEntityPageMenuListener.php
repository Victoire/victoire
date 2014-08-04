<?php
namespace Victoire\Bundle\BusinessEntityPageBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Listener\MenuListenerInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent;

/**
 * When dispatched, this listener add items to a KnpMenu
 */
class BusinessEntityPageMenuListener implements MenuListenerInterface
{
    protected $menuBuilder = null;

    /**
     * Constructor
     * @param MenuBuilder $menuBuilder
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * Add a global menu item
     * @param Event $event
     *
     * @return Menu
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function addGlobal(Event $event)
    {
        $this->mainItem = $this->menuBuilder->getTopNavbar();

        if ($this->menuBuilder->isGranted('ROLE_VICTOIRE_BET')) {
            $this
                ->mainItem
                ->addChild(
                    'menu.business_entity_page_pattern',
                    array(
                        'route' => 'victoire_businessentitypage_businessentity_index'
                    )
                )
                ->setLinkAttribute('data-toggle', 'vic-modal');
        }

        return $this->mainItem;
    }

    /**
     * Add the parent menu for a page that extends another one
     * @param PageMenuContextualEvent $event
     *
     * @return MenuBuilder
     */
    public function addContextual(PageMenuContextualEvent $event)
    {
        $mainItem = $this->menuBuilder->getTopNavbar();

        return $mainItem;
    }
}
