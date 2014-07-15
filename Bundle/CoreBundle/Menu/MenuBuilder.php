<?php
namespace Victoire\Bundle\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Knp\Menu\ItemInterface;


/**
 * Build a KnpMenu
 */
class MenuBuilder
{
    protected $menu;
    protected $factory;
    protected $securityContext;
    protected $topNavbar;
    protected $leftNavbar;

    /**
     * build a KnpMenu
     * @param FactoryInterface $factory
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(FactoryInterface $factory, $securityContext)
    {
        $this->factory = $factory;
        $this->securityContext = $securityContext;
        $this->menu    = $this->factory->createItem('root');
        $this->menu->setChildrenAttribute('class', 'nav');

        $this->leftNavbar = $this->initLeftNavbar();

        $this->topNavbar  = $this->initTopNavbar();
    }

    /**
     * create top menu defined in the contructor
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function initTopNavbar()
    {
        $this->topNavbar = $this->factory->createItem('root', array(
                'childrenAttributes' => array(
                    'id'    => "vic-topNavbar",
                    'class' => "vic-list-unstyled"
                )
            )
        );

        $this->topNavbar->addChild('menu.preview', array('uri' => '#', 'attributes' => array('class' => "vic-pull-left vic-text-center")));

        return $this->topNavbar;
    }

    /**
     * create left menu defined in the contructor
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function initLeftNavbar()
    {
        $this->leftNavbar = $this->factory->createItem('root', array(
            'childrenAttributes' => array(
                'class' => "vic-left-menu vic-list-unstyled vic-text-left"
                )
            )
        );

        $this->leftNavbar->addChild('menu.leftnavbar.stats.label', array('uri' => '#'));
        $this->leftNavbar->addChild('menu.leftnavbar.target.label', array('uri' => '#'));
        $this->leftNavbar->addChild('menu.leftnavbar.lang.label', array('uri' => '#'));
        $this->leftNavbar->addChild('menu.leftnavbar.network.label', array('uri' => '#'));
        $this->leftNavbar->addChild('menu.leftnavbar.todo.label', array('uri' => '#'));
        $this->leftNavbar->addChild('menu.leftnavbar.img.label', array('uri' => '#'));
        $this->leftNavbar->addChild('menu.leftnavbar.user.label', array('uri' => '#'));
        $this->leftNavbar->addChild('menu.leftnavbar.404.label', array('uri' => '#'));
        $this->leftNavbar->addChild('menu.leftnavbar.sitemap.label', array('uri' => '#'));
        $this->leftNavbar->addChild('menu.leftnavbar.sandbox.label', array('uri' => '#'));

        return $this->leftNavbar;
    }


    /**
     * Create the dropdown menu
     *
     * @param ItemInterface $rootItem
     * @param unknown $title
     * @param unknown $attributes
     * @param string $caret
     * @return \Knp\Menu\ItemInterface
     */
    public function createDropdownMenuItem(ItemInterface $rootItem, $title, $attributes = array(), $caret = true)
    {
        // Add child to dropdown, still normal KnpMenu usage
        $options = array_merge(
            array(
                'dropdown'   => true,
                'childrenAttributes' => array(
                    'class' => 'vic-dropdown-menu'
                ),
                'attributes' => array(
                    'class'       => 'vic-dropdown',
                    'data-toggle' => 'vic-dropdown'
                ),
                'linkAttributes' => array(
                    'class' => 'vic-dropdown-toggle'
                ),
                'uri'   => "#",
            ),
            $attributes
        );

        $menu = $rootItem->addChild($title, $options)->setExtra('caret', $caret);

        return $menu;
    }

    /**
     * return menu
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * return topNavbar
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function getTopNavbar()
    {
        return $this->topNavbar;
    }

    /**
     * return leftNavbar
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function getLeftNavbar()
    {
        return $this->leftNavbar;
    }
}
