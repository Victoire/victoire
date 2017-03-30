<?php

namespace Victoire\Bundle\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Build a KnpMenu.
 */
class MenuBuilder
{
    protected $menu;
    protected $factory;
    protected $authorizationChecker;
    protected $topNavbar;
    protected $leftNavbar;

    /**
     * build a KnpMenu.
     *
     * @param FactoryInterface     $factory
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(FactoryInterface $factory, AuthorizationChecker $authorizationChecker)
    {
        $this->factory = $factory;
        $this->authorizationChecker = $authorizationChecker;
        $this->menu = $this->factory->createItem('root');
        $this->menu->setChildrenAttribute('class', 'nav');

        $this->leftNavbar = $this->initLeftNavbar();

        $this->topNavbar = $this->initTopNavbar();
        $this->bottomLeftNavbar = $this->initBottomLeftNavbar();
        $this->bottomRightNavbar = $this->initBottomRightNavbar();
        $this->floatActionNavbar = $this->initFloatActionNavbar();
    }

    /**
     * create top menu defined in the contructor.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function initTopNavbar()
    {
        $this->topNavbar = $this->factory->createItem('root', [
                'childrenAttributes' => [
                    'id'    => 'v-navbar-top',
                    'class' => 'v-menu',
                ],
            ]
        );

        return $this->topNavbar;
    }

    /**
     * create bottom left menu defined in the contructor.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function initBottomLeftNavbar()
    {
        $this->bottomLeftNavbar = $this->factory->createItem('root', [
                'childrenAttributes' => [
                    'id'    => 'v-footer-navbar-bottom-left',
                ],
            ]
        );

        $this->createDropdownMenuItem(
            $this->getBottomLeftNavbar(),
            'menu.additionals',
            [
                'dropdown'           => true,
                'childrenAttributes' => [
                    'class' => 'v-drop v-drop__menu',
                    'id'    => 'footer-drop-navbar-left',
                ],
                'attributes' => [
                    'class'       => 'vic-dropdown',
                    'data-toggle' => 'vic-dropdown',
                ],
                'linkAttributes' => [
                    'id'                => 'v-additionals-drop',
                    'class'             => 'v-btn v-btn--transparent v-drop-trigger--no-toggle',
                    'data-flag'         => 'v-drop',
                    'data-position'     => 'topout leftin',
                    'data-droptarget'   => '#footer-drop-navbar-left',
                ],
                'uri'   => '#',
            ],
            false
        );

        return $this->bottomLeftNavbar;
    }

    /**
     * create bottom right menu defined in the contructor.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function initBottomRightNavbar()
    {
        $this->bottomRightNavbar = $this->factory->createItem('root', [
                'childrenAttributes' => [
                    'id'    => 'v-footer-navbar-bottom-right',
                ],
            ]
        );

        $this->createDropdownMenuItem(
            $this->getBottomRightNavbar(),
            'menu.template',
            [
                'dropdown'           => true,
                'childrenAttributes' => [
                    'class' => 'v-drop v-drop__menu',
                    'id'    => 'footer-drop-navbar-template',
                ],
                'attributes' => [
                    'class'       => 'vic-dropdown',
                    'data-toggle' => 'vic-dropdown',
                ],
                'linkAttributes' => [
                    'class'             => 'v-btn v-btn--sm v-btn--transparent v-drop-trigger--no-toggle',
                    'data-flag'         => 'v-drop',
                    'data-position'     => 'topout rightin',
                    'data-droptarget'   => '#footer-drop-navbar-template',
                ],
                'uri'   => '#',
            ],
            false
        );

        return $this->bottomRightNavbar;
    }

    /**
     * create bottom right menu defined in the contructor.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function initFloatActionNavbar()
    {
        $this->floatActionNavbar = $this->factory->createItem('root', [
                'childrenAttributes' => [
                    'id'    => 'v-float-container',
                ],
            ]
        );

        $this->createDropdownMenuItem(
            $this->getFloatActionNavbar(),
            'menu.floatActionDropdown',
            [
                'linkAttributes' => [
                    'class'               => 'v-btn v-btn--square v-btn--lg v-btn--fab',
                    'data-flag'           => 'v-drop v-drop-fab',
                    'data-position'       => 'bottomout center',
                    'data-droptarget'     => '#victoire-fab-dropdown',
                ],
                'childrenAttributes' => [
                    'class' => 'v-drop v-drop--fab v-drop__menu v-drop__menu',
                    'id'    => 'victoire-fab-dropdown',
                ],
            ],
            false
        );

        return $this->floatActionNavbar;
    }

    /**
     * create left menu defined in the contructor.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function initLeftNavbar()
    {
        $this->leftNavbar = $this->factory->createItem('root');

        return $this->leftNavbar;
    }

    /**
     * Create the dropdown menu.
     *
     * @param ItemInterface $rootItem
     * @param string        $title
     * @param array         $attributes
     * @param bool          $caret
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createDropdownMenuItem(ItemInterface $rootItem, $title, $attributes = [], $caret = true)
    {
        // Add child to dropdown, still normal KnpMenu usage
        $options = array_merge(
            [
                'dropdown'           => true,
                'childrenAttributes' => [
                    'class' => 'vic-dropdown-menu',
                ],
                'attributes' => [
                    'class'       => 'vic-dropdown',
                    'data-toggle' => 'vic-dropdown',
                ],
                'linkAttributes' => [
                    'class'       => 'vic-dropdown-toggle',
                    'data-toggle' => 'vic-dropdown',
                ],
                'uri'   => '#',
            ],
            $attributes
        );

        $menu = $rootItem->addChild($title, $options)->setExtra('caret', $caret);

        return $menu;
    }

    /**
     * return menu.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * return topNavbar.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function getTopNavbar()
    {
        return $this->topNavbar;
    }

    /**
     * return bottomLeftNavbar.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function getBottomLeftNavbar()
    {
        return $this->bottomLeftNavbar;
    }

    /**
     * return bottomRightNavbar.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function getBottomRightNavbar()
    {
        return $this->bottomRightNavbar;
    }

    /**
     * return floatActionNavbar.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function getFloatActionNavbar()
    {
        return $this->floatActionNavbar;
    }

    /**
     * return floatActionDropdown.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function getFloatActionDropdown()
    {
        return $this->getFloatActionNavbar()->getChild('menu.floatActionDropdown');
    }

    /**
     * return leftNavbar.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function getLeftNavbar()
    {
        return $this->leftNavbar;
    }

    /**
     * return leftNavbar.
     *
     * @param string $role The role to check
     *
     * @return bool Is the user granted ?
     */
    public function isgranted($role)
    {
        return $this->authorizationChecker->isGranted($role);
    }
}
