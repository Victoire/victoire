<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Event\Menu\BasePageMenuContextualEvent;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;

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

    /**
     * Add the parent menu for a page that extends another one
     *
     * @param BasePageMenuContextualEvent $event
     * @return MenuBuilder
     */
    public function addContextual(BasePageMenuContextualEvent $event)
    {
        $mainItem = $this->menuBuilder->getTopNavbar();

        //get the current page
        $page = $event->getPage();
        $entity = $event->getEntity();

        //are we in a business entity template with an entity given as parameter
        if (($page->getType() === BusinessEntityTemplatePage::TYPE)  && ($entity !== null)) {
            $parent = $page;
        } else {
            //get the parent
            $parent = $page->getParent();
        }

        //if there is a parent, we add the link in the top bar
        if ($parent !== null) {
            $mainItem
                ->addChild('menu.parent', array(
                    'uri' => '/'.$parent->getUrl() //we use the root url
                ));
        }

        return $mainItem;
    }
}
