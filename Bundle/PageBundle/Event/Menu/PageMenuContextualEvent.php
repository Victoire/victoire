<?php
namespace Victoire\Bundle\PageBundle\Event\Menu;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Entity\View;

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
    public function __construct(View $page)
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
