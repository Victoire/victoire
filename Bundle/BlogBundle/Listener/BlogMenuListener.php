<?php
namespace Victoire\Bundle\BlogBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Event\Menu\BasePageMenuContextualEvent;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;


/**
 * When dispatched, this listener add items to a KnpMenu
 */
class BlogMenuListener
{
    private $menuBuilder;


    /**
     * Blog menu listener constructor
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }


    /**
     * add a contextual menu item
     * @return MenuItem The added item
     */
    public function addContextual(BasePageMenuContextualEvent $event)
    {
        $mainItem = $this->getMainItem();

        $mainItem->addChild('menu.blog.settings',
            array(
                'route' => 'victoire_blog_article_settings',
                'routeParameters' => array('id' => $event->getPage()->getId())
                )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    /**
     * add global menu items
     * @return MenuItem The added item
     */
    public function addGlobal(Event $event)
    {
        $mainItem = $this->getMainItem();

        $mainItem->addChild('menu.blog.new', array(
            'route' => 'victoire_blog_article_new'
            )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    /**
     * This method returns you the main item and create it if not exists
     * @return MenuItem The main item to get
     */
    private function getMainItem()
    {
        //if not exists, create it and return it
        if ($menuPage = $this->menuBuilder->getTopNavbar()->getChild(('menu.blog'))) {
            return $menuPage;
        } else {
            //else, find it and return it
            return $this->menuBuilder->createDropdownMenuItem(
                $this->menuBuilder->getTopNavbar(),
                "menu.blog",
                array("attributes" => array( "class" => "vic-pull-left vic-text-center"))
            );
        }
    }
}
