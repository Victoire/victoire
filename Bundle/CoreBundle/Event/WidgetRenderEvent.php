<?php
namespace Victoire\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Entity\Widget;

class WidgetRenderEvent extends Event
{
    private $widget;
    private $html;

    public function __construct(Widget $widget, $html = '')
    {
        $this->widget = $widget;
        $this->html = $html;
    }

    public function getWidget()
    {
        return $this->widget;
    }

    public function getHtml()
    {
        return $this->html;
    }

}
