<?php

namespace Victoire\Bundle\MediaBundle\Helper\Menu;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The MenuBuilder will build the top menu and the side menu of the admin interface.
 */
class MenuBuilder
{
    /**
     * @var MenuAdaptorInterface[]
     */
    private $adaptors = [];

    /**
     * @var TopMenuItem[]
     */
    private $topMenuItems = null;

    /**
     * @var MenuItem|null
     */
    private $currentCache = null;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack  The request stack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
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
            /* @var $request \Symfony\Component\HttpFoundation\Request */
            $request = $this->requestStack->getMasterRequest();
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
        if ($parent === null) {
            return $this->getTopChildren();
        }
        /* @var $request Request */
        $request = $this->requestStack->getMasterRequest();
        $result = [];
        foreach ($this->adaptors as $menuAdaptor) {
            $menuAdaptor->adaptChildren($this, $result, $parent, $request);
        }

        return $result;
    }
}
