<?php

namespace Victoire\Bundle\MediaBundle\Helper\Menu;

/**
 * A MenuItem which represents an item in the top menu.
 */
class TopMenuItem extends MenuItem
{
    /**
     * @var bool
     */
    private $appearInSidebar = false;

    /**
     * @param bool $appearInSidebar
     */
    public function setAppearInSidebar($appearInSidebar)
    {
        $this->appearInSidebar = $appearInSidebar;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAppearInSidebar()
    {
        return $this->appearInSidebar;
    }
}
