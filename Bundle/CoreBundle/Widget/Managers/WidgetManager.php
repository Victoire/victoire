<?php
namespace Victoire\Bundle\CoreBundle\Widget\Managers;

use Victoire\Bundle\CoreBundle\Entity\WidgetReference;
use Victoire\Bundle\CoreBundle\Entity\Widget;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Template;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\MenuBundle\Entity\MenuItem;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\CoreBundle\Event\WidgetRenderEvent;
use Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy;
use Victoire\Bundle\CoreBundle\Event\WidgetBuildFormEvent;
use Victoire\Bundle\CoreBundle\Theme\ThemeWidgetInterface;

/**
 * Generic Widget CRUD operations
 */
class WidgetManager
{
    protected $container;
    protected $widget;
    protected $page;
    protected $widgetReference;


    /**
     * contructor
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * set page
     * @param Page $page
     */
    public function setPage(BasePage $page)
    {
        $this->page = $page;
    }



    /**
     * remove a widget
     * @param Widget $widget
     */
    public function deleteWidget(Widget $widget)
    {
        $page = $widget->getPage();
        $this->populateChildrenReferences($page, $widget, true);

        $widgetMap = $page->getWidgetMap();
        foreach ($widgetMap as $slot => $map) {
            if (false !== $key = array_search($widget->getId(), $map)) {
                unset($widgetMap[$slot][$key]);
            }
        }
        $widgetId = "vic-widget-".$widget->getId()."-container";
        $page->setWidgetMap($widgetMap);
        $em = $this->container->get('doctrine')->getManager();
        $em->persist($page);
        $em->remove($widget);
        $em->flush();

        return array(
            "success"  => true,
            "widgetId" => $widgetId
        );
    }


    /**
     * edit a widget
     * @param BasePage $basePage
     * @param Widget   $widget
     * @param bool     $delete
     * @return template
     *
     */
    public function populateChildrenReferences(BasePage $basePage, Widget $widget, $delete = false)
    {

        if (get_class($basePage) === "Victoire\Bundle\PageBundle\Entity\Template" &&
            count($basePage->getPages()) > 0
            ) {
            $em = $this->container->get('doctrine')->getManager();
            foreach ($basePage->getPages() as $page) {

                if ($delete) {
                    $widgetMap = $page->getWidgetMap();
                    foreach ($widgetMap as $slot => $map) {
                        if (false !== $key = array_search($widget->getId(), $map)) {
                            unset($widgetMap[$slot][$key]);
                        }
                    }
                    $page->setWidgetMap($widgetMap);

                } else {
                    $widgetMap = $page->getWidgetMap();
                    $widgetMap[$widget->getSlot()][] = $widget->getId();
                    $page->setWidgetMap($widgetMap);
                }

                $em->persist($page);

                $this->populateChildrenReferences($page, $widget, $delete);
            }
            $em->flush();
        }

    }

    /**
     * create a widget
     * @param string $type
     * @param string $slot
     * @param Page   $page
     * @param string $entity
     * @return template
     */
    public function createWidget($type, $slot, BasePage $page, $entity)
    {
        $manager = $this->getManager(null, $type);

        if (method_exists($manager, 'createWidget')) {
            return $manager->createWidget($type, $slot, $page, $entity, $this);
        }

        $widget = $manager->newWidget($page, $slot);
        $widget->setCurrentPage($page);
        $classes = $this->container->get('victoire_core.annotation_reader')->getBusinessClassesForWidget($widget);

        $form = $this->buildForm($manager, $widget);

        if ($entity) {
            $form = $this->buildForm($manager, $widget, $entity, $classes[$entity]);
        } else {
            $form = $this->buildForm($manager, $widget);
        }

        $request = $this->container->get('request');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $widget = $form->getData();
            $em = $this->container->get('doctrine')->getManager();


            $widget->setBusinessEntityName($entity);
            $em->persist($widget);
            $em->flush();

            $this->populateChildrenReferences($page, $widget);

            $widgetMap = $page->getWidgetMap();
            $widgetMap[$slot][] = $widget->getId();

            $page->setWidgetMap($widgetMap);

            $em->persist($page);
            $em->flush();

            return array(
                "success" => true,
                "html"    => $this->render($widget, $page)
            );
        }


        $forms = $this->renderNewWidgetForms($entity, $slot, $page, $widget);

        // if ($entity) {
        //     $forms[$entity] = $form;
        // } else {
        //     $forms['static'] = $this->buildForm($manager, $widget);
        // }

        return array(
            "success" => false,
            "html"    => $this->container->get('victoire_templating')->render(
                "VictoireCoreBundle:Widget:new.html.twig",
                array(
                    'classes' => $classes,
                    'forms'   => $forms
                )
            )
        );
    }

    public function buildWidget($type, $entity, $page, $slot, $entityName, $attrs, $fields)
    {
        $manager = $this->getManager(null, $type);
        $widget = $manager->newWidget($page, $slot);
        $entityProxy = new EntityProxy();
        $method = 'set'.ucfirst($entityName);
        $entityProxy->$method($entity);
        $entityProxy->setWidget($widget);
        $widget->setEntity($entityProxy);
        $widget->setBusinessEntityName($entityName);
        foreach ($attrs as $key => $value) {
            $widget->{'set'.ucfirst($key)}($value);

        }
        $widget->setFields($fields);

        return $widget;
    }

    /**
     * Build a static widget by giving it the content as
     * @param  string $type       The widget type
     * @param  Page   $page       The page
     * @param  string $slot       The slot name
     * @param  string $entityName The entity name
     * @param  array  values      The values to set
     * @return Widget             The widget
     */
    public function buildStaticWidget($type, $page, $slot, $values)
    {
        $manager = $this->getManager(null, $type);
        $widget  = $manager->newWidget($page, $slot);
        foreach ($values as $key => $value) {
            $widget->{"set".ucFirst($key)}($value);
        }

        return $widget;
    }

    /**
     * Generates new forms for each available business entities
     * @param string   $type
     * @param string   $slot
     * @param BasePage $page
     * @param Widget   $widget
     *
     * @return collection of forms
     */
    private function renderNewWidgetForms($type, $slot, BasePage $page, Widget $widget)
    {
        $annotationReader = $this->container->get('victoire_core.annotation_reader');
        $classes = $annotationReader->getBusinessClassesForWidget($widget);
        $manager = $this->getManager($widget);

        $forms['static'] = $this->renderNewForm($this->buildForm($manager, $widget), $widget, $slot, $page);

        // Build each form relative to business entities
        foreach ($classes as $entityName => $namespace) {
            $form = $this->buildForm($manager, $widget, $entityName, $namespace);
            $forms[$entityName] = $this->renderNewForm($form, $widget, $slot, $page, $entityName);
        }

        return $forms;
    }

    /**
     * Generates forms for each available business entities
     * @param string   $widget
     *
     * @return collection of forms
     */
    public function renderWidgetForms($widget)
    {
        $manager = $this->getManager($widget);
        $classes = $this->container->get('victoire_core.annotation_reader')->getBusinessClassesForWidget($widget);

        $forms['static'] = $manager->renderForm($this->buildForm($manager, $widget), $widget);

        // Build each form relative to business entities
        foreach ($classes as $entityName => $namespace) {
            $form = $this->buildForm($manager, $widget, $entityName, $namespace);
            $forms[$entityName] = $manager->renderForm($form, $widget, $entityName);
        }

        return $forms;
    }

    /**
     * new widget
     * @param string   $type
     * @param string   $slot
     * @param BasePage $page
     * @return template
     */
    public function newWidget($type, $slot, BasePage $page)
    {
        $manager = $this->getManager(null, $type);
        $widget = $manager->newWidget($page, $slot);

        $classes = $this->container->get('victoire_core.annotation_reader')->getBusinessClassesForWidget($widget);
        $forms = $this->renderNewWidgetForms($type, $slot, $page, $widget);

        return array(
            "html" => $this->container->get('victoire_templating')->render(
                "VictoireCoreBundle:Widget:Form/new.html.twig",
                array(
                    'classes' => $classes,
                    'widget'  => $widget,
                    'forms'   => $forms
                )
            )
        );
    }

    /**
     * edit a widget
     * @param Widget $widget
     * @param string $entity
     * @return template
     */
    public function edit(Widget $widget, $entity = null)
    {
        $request = $this->container->get('request');
        $classes = $this->container->get('victoire_core.annotation_reader')->getBusinessClassesForWidget($widget);
        $manager = $this->getManager($widget);

        if (method_exists($manager, 'edit')) {
            return $manager->edit($widget, $entity, $this);
        }

        $form = $this->buildForm($manager, $widget);

        if ($entity) {
            $form = $this->buildForm($manager, $widget, $entity, $classes[$entity]);
        }
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getManager();
            $widget->setBusinessEntityName($entity);
            $em->persist($widget);
            $em->flush();

            return array(
                "success"  => true,
                "html"     => $this->render($widget),
                "widgetId" => "vic-widget-".$widget->getId()."-container"
            );
        }

        $forms = $this->renderWidgetForms($widget);

        return array(
            "success"  => false,
            "html"     => $this->container->get('victoire_templating')->render(
                "VictoireCoreBundle:Widget:Form/edit.html.twig",
                array(
                    'classes' => $classes,
                    'forms'   => $forms,
                    'widget'  => $widget
                )
            )
        );
    }


    /**
     * render a widget
     * @param Widget $widget
     * @return template
     */
    public function render(Widget $widget)
    {
        $html = '';
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_PRE_RENDER, new WidgetRenderEvent($widget, $html));

        $html .= $this->getManager($widget)->render($widget);
        if ($this->container->get('security.context')->isGranted('ROLE_VICTOIRE')) {
            $html .= $this->renderActions($widget->getSlot(), $widget->getPage());
        }
        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_POST_RENDER, new WidgetRenderEvent($widget, $html));

        return $html;
    }

    /**
     * tells if current widget is a reference
     * @param Widget   $widget
     * @param BasePage $page
     * @return boolean
     */
    public function isReference(Widget $widget, BasePage $page)
    {
        return $widget->getPage()->getId() !== $page->getId();
    }

    /**
     * render widget actions
     * @param Widget $widget
     * @return template
     */
    public function renderWidgetActions(Widget $widget)
    {
        return $this->container->get('victoire_templating')->render(
            'VictoireCoreBundle:Widget:widgetActions.html.twig',
            array(
                "widget" => $widget,
                "page" => $widget->getCurrentPage(),
            )
        );
    }

    /**
     * render slot actions
     * @param string $slot
     * @param Page   $page
     * @return template
     */
    public function renderActions($slot, BasePage $page, $first = false)
    {
        $slots = $this->container->getParameter('victoire_core.slots');

        $max = null;
        if (array_key_exists('max', $slots[$slot])) {
            $max = $slots[$slot]['max'];
        }

        return $this->container->get('victoire_templating')->render(
            "VictoireCoreBundle:Widget:actions.html.twig",
            array(
                "slot"    => $slot,
                "page"    => $page,
                'widgets' => array_keys($slots[$slot]['widgets']),
                'max'     => $max,
                'first'   => $first,
            )
        );
    }


    /**
     * get specific widget for provided widget type
     * @param Widget $widget
     * @param string $type
     * @return manager
     */
    public function getManager($widget = null, $type = null)
    {
        $renderer = $this->container->get($this->getWidgetType($widget, $type)."_manager");

        return $renderer;
    }


    /**
     * return widget type
     * @param widget $widget
     * @param string $type
     * @return widget type
     */
    public function getWidgetType($widget, $type = null)
    {
        if ($type !== null) {
           $widgetClass = array("Widget".ucfirst($type));
        } else {
           $widgetClass = explode('\\', get_class($widget));
        }

        $widgetName = str_replace('Widget', '', end($widgetClass));
        $widgetType = "widget_".strtolower($widgetName);

        return $widgetType;
    }

    /**
     * find widget by page and by slot
     * @param Page   $page
     * @param string $slot
     * @return Collection widgets
     */
    public function findByPageBySlot(BasePage $page, $slot)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $widgetRepo = $em->getRepository('VictoireCoreBundle:Widget');

        return $widgetRepo->findByPageBySlot($page, $slot);
    }



    /**
     * compute the widget map for page
     * @param BasePage   $page
     * @param array      $sortedWidgets
     */
    public function computeWidgetMap(BasePage $page, $sortedWidgets)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $widgetMap = array();
        $widgetSlots = array();

        foreach ($sortedWidgets as $slot => $widgetContainers) {
            $slot = str_replace('vic-slot-', '', $slot);
            foreach ($widgetContainers as $containerId) {
                $id = preg_replace('/[^0-9]*/', '', $containerId);
                if ($id !== '') {
                    $widgetSlots[$id] = $slot;
                    $widgetMap[$slot][] = $id;
                }
            }
        }

        $widgets = $em->getRepository('VictoireCoreBundle:Widget')->findAllIn(array_keys($widgetSlots));
        foreach ($widgets as $widget) {
            $id = $widget->getId();
            $isAllowed = $this->isWidgetAllowedForSlot($widget, $widgetSlots[$id]);
            if (!$isAllowed) {
                throw new \Exception('This widget is not allowed in this slot');
            }
        }

        $page->setWidgetMap($widgetMap);
        $em->persist($page);
        $em->flush();

    }

    /**
     * check if widget is allowed for slot
     * @param Widget $widget
     * @param string $slot
     * @return bool
     */
    public function isWidgetAllowedForSlot($widget, $slot)
    {
        $widgetType = str_replace('widget_', '', $this->getWidgetType($widget));
        $slots = $this->container->getParameter('victoire_core.slots');
        if ($widget instanceof ThemeWidgetInterface) {
            $manager = $this->getManager($widget);
            $widgetName = $manager->getWidgetName();

            return (array_key_exists($widgetName, $slots[$slot]['widgets']) && $slots[$slot]['widgets'][$widgetName] == null) || !array_key_exists('themes', $slots[$slot]['widgets'][$widgetName]) || in_array($widgetType, $slots[$slot]['widgets'][$widgetName]['themes']);
        }
        return !empty($slots[$slot]) && (array_key_exists($widgetType, $slots[$slot]['widgets']));


    }

    /**
     * build widget form and dispatch event
     * @param Manager $manager
     * @param Widget  $widget
     * @param string  $entityName
     * @param string  $namespace
     * @return Form
     */
    public function buildForm($manager, $widget, $entityName = null, $namespace = null)
    {
        $form = $manager->buildForm($widget, $entityName, $namespace);

        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_BUILD_FORM, new WidgetBuildFormEvent($widget, $form));

        return $form;
    }

    /**
     * render a new form
     * @param Form   $form
     * @param Widget $widget
     * @param string $slot
     * @param Page   $page
     * @param string $entityName
     * @return Collection widgets
     */
    public function renderNewForm($form, $widget, $slot, $page, $entityName = null)
    {
        $manager = $this->getManager($widget);

        return $manager->renderNewForm($form, $widget, $slot, $page, $entityName);
    }
}
