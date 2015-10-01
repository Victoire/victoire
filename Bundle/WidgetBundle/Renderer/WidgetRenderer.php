<?php

namespace Victoire\Bundle\WidgetBundle\Renderer;

use Symfony\Component\DependencyInjection\Container;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Event\WidgetRenderEvent;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Victoire\Bundle\WidgetBundle\Model\Widget;

class WidgetRenderer
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * render the Widget.
     *
     * @param Widget $widget
     * @param View   $view
     *
     * @return widget show
     */
    public function render(Widget $widget, View $view)
    {
        //the mode of display of the widget
        $mode = $widget->getMode();

        //if entty is given and it's not the object, retrive it and set the entity for the widget
        if ($mode == Widget::MODE_BUSINESS_ENTITY && $view instanceof BusinessPage) {
            $widget->setEntity($view->getBusinessEntity());
        }

        //the templating service
        $templating = $this->container->get('victoire_templating');

        //the content of the widget
        $parameters = $this->container->get('victoire_widget.widget_content_resolver')->getWidgetContent($widget);

        //the template displayed is in the widget bundle (with the potential theme)
        $showView = 'show'.ucfirst($widget->getTheme());
        $templateName = $this->container->get('victoire_widget.widget_helper')->getTemplateName($showView, $widget);

        return $templating->render(
            $templateName,
            $parameters
        );
    }

    /**
     * render a widget.
     *
     * @param Widget $widget
     * @param View   $view
     *
     * @return string
     */
    public function renderContainer(Widget $widget, View $view)
    {
        $dispatcher = $this->container->get('event_dispatcher');

        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_PRE_RENDER, new WidgetRenderEvent($widget));

        $html = '<div class="vic-widget-container" data-id="'.$widget->getId().'" id="vic-widget-'.$widget->getId().'-container">';
        $html .= $this->render($widget, $view);
        $html .= '</div>';

        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_POST_RENDER, new WidgetRenderEvent($widget, $html));

        return $html;
    }

    /**
     * prepare a widget to be rendered asynchronously.
     *
     * @param int $widgetId
     *
     * @return string
     */
    public function prepareAsynchronousRender($widgetId)
    {
        $ngControllerName = 'widget'.$widgetId.'AsynchronousLoadCtrl';
        $ngDirectives = sprintf('ng-controller="WidgetAsynchronousLoadController as %s" class="vic-widget" ng-init="%s.init(%d)" ng-bind-html="html"', $ngControllerName, $ngControllerName, $widgetId);
        $html = sprintf('<div class="vic-widget-container vic-widget-asynchronous" data-id="%d" id="vic-widget-%d-container" %s></div>', $widgetId, $widgetId, $ngDirectives);

        return $html;
    }

    /**
     * render widget unlink action.
     *
     * @param int  $widgetId
     * @param View $view
     *
     * @return string
     */
    public function renderUnlinkActionByWidgetId($widgetId, $view)
    {
        return $this->container->get('victoire_templating')->render(
            'VictoireCoreBundle:Widget:widgetUnlinkAction.html.twig',
            [
                'widgetId' => $widgetId,
                'view'     => $view,
            ]
        );
    }

    /**
     * render slot actions.
     *
     * @param Slot   $slot
     * @param string $options
     *
     * @return string
     */
    public function renderActions($slot, $options = [])
    {
        return $this->container->get('victoire_templating')->render(
            'VictoireCoreBundle:Widget:actions.html.twig',
            [
                'slot'     => $slot,
                'options'  => $options,
            ]
        );
    }

    /**
     * Compute slot options.
     *
     * @param Slot  $slotId
     * @param array $options
     *
     * @return string
     */
    public function computeOptions($slotId, $options = [])
    {
        $slots = $this->container->getParameter('victoire_core.slots');

        $availableWidgets = $this->container->getParameter('victoire_core.widgets');
        $widgets = [];

        //If the slot is declared in config
        if (!empty($slots[$slotId]) && !empty($slots[$slotId]['widgets'])) {
            //parse declared widgets
            $slotWidgets = array_keys($slots[$slotId]['widgets']);
        } elseif (!empty($options['availableWidgets'])) {
            $slotWidgets = $options['availableWidgets'];
        } else {
            //parse all widgets
            $slotWidgets = array_keys($availableWidgets);
        }

        foreach ($slotWidgets as $slotWidget) {
            $widgetParams = $availableWidgets[$slotWidget];
            // if widget has a parent
            if (!empty($widgetParams['parent'])) {
                // place widget under its parent
                $widgets[$widgetParams['parent']]['children'][$slotWidget]['params'] = $widgetParams;
            } else {
                $widgets[$slotWidget]['params'] = $widgetParams;
            }
        }
        $slots[$slotId]['availableWidgets'] = $widgets;
        if (isset($options['max'])) {
            $slots[$slotId]['max'] = $options['max'];
        }

        return $slots[$slotId];
    }

    /**
     * Get the extra classes for the css.
     *
     * @return string The classes
     */
    public function getExtraCssClass(Widget $widget)
    {
        $cssClass = 'vic-widget-'.strtolower($this->container->get('victoire_widget.widget_helper')->getWidgetName($widget));

        return $cssClass;
    }
}
