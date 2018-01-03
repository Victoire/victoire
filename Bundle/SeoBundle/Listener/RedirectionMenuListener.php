<?php

namespace Victoire\Bundle\SeoBundle\Listener;

use Knp\Menu\ItemInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;

/**
 * When dispatched, this listener add items to a KnpMenu.
 */
class RedirectionMenuListener
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
        $bottomRightNavbar = $this->menuBuilder->getBottomRightNavbar();

        $bottomRightNavbar
            ->addChild('<i class="fa fa-exclamation"></i>', [
                'route'           => 'victoire_404_index',
                'linkAttributes'  => [
                    'class' => 'v-btn v-btn--sm v-btn--transparent',
                    'id'    => 'v-404-link',
                ],
            ])->setLinkAttribute('data-toggle', 'vic-modal');

        $bottomRightNavbar
            ->addChild('<i class="fa fa-random"></i>', [
                'route'           => 'victoire_redirection_index',
                'linkAttributes'  => [
                    'class' => 'v-btn v-btn--sm v-btn--transparent',
                    'id'    => 'v-redirection-link',
                ],
            ])->setLinkAttribute('data-toggle', 'vic-modal');

        return $bottomRightNavbar;
    }
}
