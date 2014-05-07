<?php
namespace Victoire\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Entity\Widget;

class WidgetBuildFormEvent extends Event
{
    private $widget;
    private $form;

    public function __construct(Widget $widget, $form = '')
    {
        $this->widget = $widget;
        $this->form = $form;
    }

    public function getWidget()
    {
        return $this->widget;
    }

    public function getForm()
    {
        return $this->form;
    }

}
