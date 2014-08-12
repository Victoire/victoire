<?php
namespace Victoire\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 *
 * @author Paul Andrieux
 *
 */
class WidgetBuildFormEvent extends Event
{
    private $widget;
    private $form;

    /**
     * Constructor
     *
     * @param Widget $widget
     * @param string $form
     */
    public function __construct(Widget $widget, $form = '')
    {
        $this->widget = $widget;
        $this->form = $form;
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
     * Get the form
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }
}
