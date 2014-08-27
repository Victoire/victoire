<?php
namespace Victoire\Bundle\PageBundle\Event\Menu;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * This Event provides current Page entity to which listen it
 */
class PageMenuContextualEvent extends Event
{
    protected $page;

    /**
     * Constructor
     *
     * @param Page $page
     */
    public function __construct(BasePage $page)
    {
        $this->page = $page;
    }

    /**
     * get page
     *
     * @return Page The current page
     */
    public function getPage()
    {
        return $this->page;
    }
}
