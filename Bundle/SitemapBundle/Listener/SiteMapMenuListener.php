<?php

namespace Victoire\Bundle\SitemapBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;

/**
 * When dispatched, this listener add items to a KnpMenu.
 */
class SiteMapMenuListener
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
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function addGlobal(Event $event)
    {
        $this->mainItem = $this->menuBuilder->getLeftNavbar();

        $this->mainItem
            ->addChild('menu.sitemap', [
                'route' => 'victoire_sitemap_reorganize',
            ])
            ->setLinkAttribute('data-toggle', 'vic-modal');

        return $this->mainItem;
    }
}
