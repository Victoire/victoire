<?php
namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Victoire\Bundle\CoreBundle\Event\WidgetQueryEvent;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\CoreBundle\Event\WidgetBuildFormEvent;
use Victoire\Bundle\CoreBundle\Theme\ThemeWidgetInterface;

class WidgetSubscriber implements EventSubscriberInterface
{

    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // VictoireCmsEvents::WIDGET_PRE_RENDER => array(
            // ),
            // VictoireCmsEvents::WIDGET_POST_RENDER => array(
            // ),
            VictoireCmsEvents::WIDGET_POST_QUERY => array(
                array('buildFilterQuery'),
            ),
            VictoireCmsEvents::WIDGET_BUILD_FORM => array(
                array('addThemeField'),
            ),
        );
    }

    public function buildFilterQuery(WidgetQueryEvent $event)
    {
        if ($this->container->has('victoire_core.filter_chain')) {

            $request = $event->getRequest();
            $widget = $event->getWidget();
            $filters = $request->query->get('filter');
            $listId = $filters['list'];
            $qb = $event->getQb();

            if ($listId == $widget->getId()) {
                unset($filters['list']);
                foreach ($this->container->get('victoire_core.filter_chain')->getFilters() as $name => $filter) {
                    if (!empty($filters[$name])) {
                        $filter->buildQuery($qb, $filters[$name]);
                    }
                }
                $filterData = $request->query->get('filter');
                $categories = $filterData['category_filter']['categories'];

            }
        }
    }

    public function addThemeField(WidgetBuildFormEvent $event)
    {
        $form = $event->getForm();
        $widget = $event->getWidget();
        $themeChain = $this->container->get('victoire_core.theme_chain');
        $manager = $this->container->get('widget_manager')->getManager($widget);
        if ($widget instanceof ThemeWidgetInterface) {
            $widgetClass = get_parent_class($widget);
            $currentWidget = $manager->getThemeName();
        } else {
            $widgetClass = get_class($widget);
            $currentWidget = $this->container->get('widget_manager')->getWidgetType($widget);
        }
        $widgets = $this->container->getParameter('victoire_core.widgets');
        foreach ($this->container->getParameter('victoire_core.widgets') as $widgetName => $params) {
            if ($params['class'] === $widgetClass) {
                break;
            }
        }
        $themeObjs = $themeChain->getThemes($widgetClass);
        if (count($themeObjs) > 0) {

            $themes = array($widgetName => 'widget.form.theme.default');
            foreach ($themeObjs as $themeObj) {
                $themes[$themeObj->getName()] = 'widget.form.theme.' . $themeObj->getName();
            }

            $form->add('theme', 'choice',
                array(
                    'mapped' => false,
                    'label' => 'widget.form.theme.label',
                    'choices' => $themes,
                    'data' => $currentWidget,
                    'attr' => array('class' => 'theme-choices'),
                )
            );
        }
    }

}
