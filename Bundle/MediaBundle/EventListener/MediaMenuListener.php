<?php
namespace Victoire\Bundle\MediaBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Victoire\Bundle\CoreBundle\Listener\MenuListenerInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent;


/**
 * When dispatched, this listener add items to a KnpMenu
 */
class MediaMenuListener
{
    private $menuBuilder;

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
            ->addChild('menu.media', array(
                'uri' => '#'
            ))
            ->setLinkAttribute('id', 'media-manager');

        return $this->mainItem;

    }
}
