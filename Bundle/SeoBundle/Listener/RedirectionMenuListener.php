<?php

namespace Victoire\Bundle\SeoBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;

/**
 * When dispatched, this listener add items to a KnpMenu.
 */
class RedirectionMenuListener
{
    private $menuBuilder;
    private $mainItem;
    private $router;

    /**
     * RedirectionMenuListener constructor.
     *
     * @param MenuBuilder $menuBuilder
     * @param Router      $router
     */
    public function __construct(MenuBuilder $menuBuilder, Router $router)
    {
        $this->menuBuilder = $menuBuilder;
        $this->router = $router;
    }

    /**
     * Add global menu items.
     *
     * @return \Knp\Menu\ItemInterface <\Knp\Menu\ItemInterface, NULL>
     */
    public function addGlobal()
    {
        $this->mainItem = $this->menuBuilder->getLeftNavbar();

        $dropdown = $this->menuBuilder->createDropdownMenuItem($this->mainItem, 'menu.left.redirection.mainItem.label')
            ->setAttribute('id', 'menu-redirection-main-item');

        $dropdown->addChild('menu.left.redirection.subItem.404.label', [
            'route' => 'victoire_404_index',
            'title' => 'erreurs_404',
        ])
            ->setAttribute('id', 'menu-404-sub-item')
            ->setLinkAttribute('ic-get-from', $this->router->generate('victoire_404_index'))
            ->setLinkAttribute('ic-target', '#vic-modal-container');

        $dropdown->addChild('menu.left.redirection.subItem.redirection.label', [
            'route' => 'victoire_redirection_index',
            'title' => 'redirections',
        ])
            ->setAttribute('id', 'menu-redirection-sub-item')
            ->setLinkAttribute('ic-get-from', $this->router->generate('victoire_redirection_index'))
            ->setLinkAttribute('ic-target', '#vic-modal-container');

        return $this->mainItem;
    }
}
