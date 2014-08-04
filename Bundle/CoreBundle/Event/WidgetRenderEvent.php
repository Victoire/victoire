<?php
namespace Victoire\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 *
 * @author Paul Andrieux
 *
 */
class WidgetRenderEvent extends Event
{
    private $widget;
    private $html;

    /**
     * Constructor
     *
     * @param Widget $widget
     * @param string $html
     */
    public function __construct(Widget $widget, $html = '')
    {
        $this->widget = $widget;
        $this->html = $html;
    }

    /**
     * Get the widget
     *
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * Get the html
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }
}
