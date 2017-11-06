<?php

namespace Victoire\Bundle\SeoBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;

/**
 * When dispatched, this listener add items to a KnpMenu.
 */
class RedirectionMenuListener
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
     * @return \Knp\Menu\ItemInterface <\Knp\Menu\ItemInterface, NULL>
     */
    public function addGlobal()
    {
        $this->mainItem = $this->menuBuilder->getLeftNavbar();

        $dropdown = $this->menuBuilder->createDropdownMenuItem($this->mainItem, 'Redirections');

        $dropdown->addChild('Erreurs 404', [
            'route' => 'victoire_404_index'
        ])->setLinkAttribute('data-toggle', 'vic-modal');

        $dropdown->addChild('Redirections', [
            'route' => 'victoire_redirection_index'
        ])->setLinkAttribute('data-toggle', 'vic-modal');

        return $this->mainItem;
    }
}
