<?php
namespace Victoire\Bundle\PageBundle\Event\Menu;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * This Event provides current Page entity to which listen it
 */
class PageMenuContextualEvent extends Event
{
    protected $page;
    protected $entity;

    /**
     * Constructor
     *
     * @param Page   $page
     * @param string $entity
     */
    public function __construct(Page $page, $entity = null)
    {
        $this->page = $page;
        $this->entity = $entity;
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

    /**
     * Get the entity
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
