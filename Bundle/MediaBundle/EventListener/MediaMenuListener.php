<?php
namespace Kunstmaan\MediaBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;


/**
 * When dispatched, this listener add items to a KnpMenu
 */
class MediaMenuListener
{
    private $menuBuilder;
    private $ed;


    /**
     * {@inheritDoc}
     */
    public function __construct(MenuBuilder $menuBuilder, $ed)
    {
        $this->ed = $ed;
        $this->menuBuilder = $menuBuilder;
    }



    public function onKernelRequest(GetResponseEvent $event)
    {

        $this->ed->addListener("victoire_core.media_menu.global",
            array($this, 'addGlobal')
        );

        $this->ed->dispatch('victoire_core.media_menu.global');


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

    // private function getMainItem()
    // {
    //     if ($menuMedia = $this->menuBuilder->getTopNavbar()->getChild(('menu.media'))) {
    //         return $menuMedia;
    //     } else {
    //         return $this->menuBuilder->createDropdownMenuItem(
    //             $this->menuBuilder->getTopNavBar(),
    //             "menu.media",
    //             array("attributes" => array( "class" => "vic-pull-left vic-text-center"))
    //         );
    //     }
    // }
}
