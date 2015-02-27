<?php
namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\CoreBundle\Event\WidgetBuildFormEvent;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Behat\Behat\Exception\Exception;

/**
 *
 * @author Paul Andrieux
 *
 */
class WidgetSubscriber implements EventSubscriberInterface
{
    protected $container;

    /**
     *
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Get the subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        $events = array(
                VictoireCmsEvents::WIDGET_BUILD_FORM => array(
                    array('addThemeField')
                )
        );

        return $events;
    }

    /**
     * Add the theme field for the widget form
     *
     * @param WidgetBuildFormEvent $event
     *
     * @throws Exception
     */
    public function addThemeField(WidgetBuildFormEvent $event)
    {
        //services
        $container = $this->container;

        $themeHelper = $container->get('victoire_core.theme_chain');

        //by default the theme is added
        $addThemeField = true;

        $form = $event->getForm();
        $widget = $event->getWidget();

        // if we are in edition mode, do not add theme field
        // the theme  field is disabled while the data is not given between theme forms
        // this test should be removed when the data is keep between themes
        if ($widget->getId() !== null) {
            $addThemeField = false;
        }

        //the widget name
        $widgetName = $this->getWidgetName($widget);

        if ($widgetName === null) {
            throw new \Exception('The name of the widget was not found, please check that the config.yml of the bundle contains the entry victoire_core.widgets.xxxxx.');
        }

        //get the list of themes for the widget
        $themes = $themeHelper->getThemes($widgetName);

        //if the widget has some theme
        if (count($themes) === 0) {
            $addThemeField = false;
        }

        //if the widget has some theme
        if ($addThemeField) {
            //the list of select items
            $themeForSelect = array();

            //add the default value
            $themeForSelect[$widgetName] = 'widget.form.theme.default';

            //create the list
            foreach ($themes as $theme) {
                //add values
                $themeForSelect[$theme] = 'widget.form.theme.'.$theme;
            }

            $form->add('theme', 'choice',
                array(
                    'mapped' => false,
                    'label' => 'widget.form.theme.label',
                    'choices' => $themeForSelect,
                    'data' => $widgetName,
                    'attr' => array('class' => 'theme-choices'),
                )
            );
        }
    }

    /**
     * Get the widgetName
     *
     * @param Widget $widget
     *
     * @return string The name of the widget
     */
    protected function getWidgetName(Widget $widget)
    {
        //services
        $container = $this->container;

        $widgetName = null;

        //the widget manager class name
        $widgetClass = get_class($widget);

        //get the name of the widgets
        foreach ($container->getParameter('victoire_core.widgets') as $params) {
            if ($params['class'] === $widgetClass) {
                $widgetName = $params['name'];
                break;
            }
        }

        return $widgetName;
    }
}
