<?php

namespace Victoire\Bundle\SeoBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\Event;
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
     * @param Router $router
     */
    public function __construct(MenuBuilder $menuBuilder, Router $router)
    {
        $this->menuBuilder = $menuBuilder;
        $this->router = $router;
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
            'route' => 'victoire_404_index',
            'title' => 'erreurs_404'
        ])
            ->setLinkAttribute('ic-get-from', $this->router->generate('victoire_404_index'))
            ->setLinkAttribute('ic-target', '#vic-modal-container')
        ;

        $dropdown->addChild('Redirections', [
            'route' => 'victoire_redirection_index'
        ])
            ->setLinkAttribute('ic-get-from', $this->router->generate('victoire_redirection_index'))
            ->setLinkAttribute('ic-target', '#vic-modal-container')
        ;

        return $this->mainItem;
    }
}
