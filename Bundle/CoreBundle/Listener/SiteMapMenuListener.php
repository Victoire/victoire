<?php
namespace Victoire\Bundle\CoreBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Event\SiteMapContextualEvent;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;


/**
 * When dispatched, this listener add items to a KnpMenu
 */
class SiteMapMenuListener
{
    private $menuBuilder;
    private $mainItem;


    /**
     * {@inheritDoc}
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }


    /**
     * add global menu items
     */
    public function addGlobal(Event $event)
    {
        $this->mainItem = $this->menuBuilder->getTopNavbar();

        $this->mainItem
            ->addChild('menu.sitemap', array(
                'route' => 'victoire_core_page_sitemap'
            ))
            ->setLinkAttribute('data-toggle', 'vic-modal');

        return $this->mainItem;
    }
}
