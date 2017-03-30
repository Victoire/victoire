<?php

namespace Victoire\Bundle\MediaBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;

/**
 * When dispatched, this listener add items to a KnpMenu.
 */
class MediaMenuListener
{
    private $menuBuilder;

    /**
     * {@inheritdoc}
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * add global menu items.
     */
    public function addGlobal(Event $event)
    {
        $topNavbar = $this->menuBuilder->getTopNavbar();

        $topNavbar
            ->addChild('menu.media', [
                'uri'        => '#',
            ])
            ->setLinkAttribute('id', 'media-manager');
    }
}
