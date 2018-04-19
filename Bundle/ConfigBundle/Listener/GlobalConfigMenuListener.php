<?php

namespace Victoire\Bundle\ConfigBundle\Listener;

use Knp\Menu\ItemInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;

/**
 * When dispatched, this listener add item to a KnpMenu.
 */
class GlobalConfigMenuListener
{
    private $menuBuilder;

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
     * Add global menu items.
     *
     * @return ItemInterface
     */
    public function addGlobal()
    {
        return $this->menuBuilder
            ->getBottomLeftNavbar()
            ->getChild('menu.additionals')
            ->addChild(
                'menu.global_config',
                [
                    'route'          => 'victoire_config_global_edit',
                    'linkAttributes' => [
                        'class' => 'v-drop__anchor',
                    ],
                ]
            )
            ->setLinkAttribute('data-toggle', 'vic-modal');
    }
}
