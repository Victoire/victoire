<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Event\Menu\BasePageMenuContextualEvent;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;


/**
 * When dispatched, this listener add items to a KnpMenu
 */
class BusinessEntityTemplateMenuListener
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
     * Add a global menu item
     *
     * @param Event $event
     *
     * @return Menu
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function addGlobal(Event $event)
    {
        $this->mainItem = $this->menuBuilder->getTopNavbar();

        $this->mainItem
            ->addChild('menu.business_entity_template', array(
                'route' => 'victoire_businessentitytemplate_businessentity_index'
            ));

        return $this->mainItem;
    }
}
