<?php

namespace Victoire\Bundle\MediaBundle\Helper\Menu;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * The MenuBuilder will build the top menu and the side menu of the admin interface.
 */
class MenuBuilder
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var MenuAdaptorInterface[]
     */
    private $adaptors = [];

    /**
     * @var TopMenuItem[]
     */
    private $topMenuItems = null;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var MenuItem|null
     */
    private $currentCache = null;

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator The translator
     * @param ContainerInterface  $container  The container
     *
     * @TODO: this should only have a Request parameter
     */
    public function __construct(TranslatorInterface $translator, ContainerInterface $container)
    {
        $this->translator = $translator;
        $this->container = $container;
    }

    /**
     * Add menu adaptor.
     *
     * @param MenuAdaptorInterface $adaptor
     */
    public function addAdaptMenu(MenuAdaptorInterface $adaptor)
    {
        $this->adaptors[] = $adaptor;
    }

    /**
     * Get current menu item.
     *
     * @return MenuItem|null
     */
    public function getCurrent()
    {
        if ($this->currentCache !== null) {
            return $this->currentCache;
        }
        /* @var $active MenuItem */
        $active = null;
        do {
            /* @var MenuItem[] $children */
            $children = $this->getChildren($active);
            $foundActiveChild = false;
            foreach ($children as $child) {
                if ($child->getActive()) {
                    $foundActiveChild = true;
                    $active = $child;
                    break;
                }
            }
        } while ($foundActiveChild);
        $this->currentCache = $active;

        return $active;
    }

    /**
     * Get breadcrumb path for current menu item.
     *
     * @return MenuItem[]
     */
    public function getBreadCrumb()
    {
        $result = [];
        $current = $this->getCurrent();
        while (!is_null($current)) {
            array_unshift($result, $current);
            $current = $current->getParent();
        }

        return $result;
    }

    /**
     * Get top parent menu of current menu item.
     *
     * @return TopMenuItem|null
     */
    public function getLowestTopChild()
    {
        $current = $this->getCurrent();
        while (!is_null($current)) {
            if ($current instanceof TopMenuItem) {
                return $current;
            }
            $current = $current->getParent();
        }
    }

    /**
     * Get all top menu items.
     *
     * @return MenuItem[]
     */
    public function getTopChildren()
    {
        if (is_null($this->topMenuItems)) {
            /* @var $request Request */
            $request = $this->container->get('request');
            $this->topMenuItems = [];
            foreach ($this->adaptors as $menuAdaptor) {
                $menuAdaptor->adaptChildren($this, $this->topMenuItems, null, $request);
            }
        }

        return $this->topMenuItems;
    }

    /**
     * Get immediate children of the specified menu item.
     *
     * @param MenuItem $parent
     *
     * @return MenuItem[]
     */
    public function getChildren(MenuItem $parent = null)
    {
        if ($parent == null) {
            return $this->getTopChildren();
        }
        /* @var $request Request */
        $request = $this->container->get('request');
        $result = [];
        foreach ($this->adaptors as $menuAdaptor) {
            $menuAdaptor->adaptChildren($this, $result, $parent, $request);
        }

        return $result;
    }
}
