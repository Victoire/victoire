<?php
namespace Victoire\Bundle\CoreBundle\Event\Menu;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\PageBundle\Entity\BasePage;


/**
 * This Event provides current Page entity to which listen it
 */
class BasePageMenuContextualEvent extends Event
{
    protected $page;


    /**
     * {@inheritDoc}
     */
    public function __construct(BasePage $page)
    {
        $this->page = $page;
    }


    /**
     * get page
     */
    public function getPage()
    {
        return $this->page;
    }
}
