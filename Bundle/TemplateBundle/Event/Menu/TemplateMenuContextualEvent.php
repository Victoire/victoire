<?php

namespace Victoire\Bundle\TemplateBundle\Event\Menu;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\TemplateBundle\Entity\Template;

/**
 * This Event provides current Page entity to which listen it.
 */
class TemplateMenuContextualEvent extends Event
{
    protected $template;

    /**
     * {@inheritdoc}
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
     * get Template.
     *
     * @return Template $template
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
