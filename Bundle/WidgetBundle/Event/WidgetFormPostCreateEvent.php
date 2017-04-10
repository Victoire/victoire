<?php

namespace Victoire\Bundle\WidgetBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Victoire\Bundle\WidgetBundle\Form\WidgetOptionsContainer;

class WidgetFormPostCreateEvent extends Event
{
    public $builder;

    /**
     * WidgetFormPostCreateEvent constructor.
     *
     * @param FormInterface $builder
     */
    public function __construct(FormInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return FormInterface
     */
    public function getBuilder()
    {
        return $this->builder;
    }
}
