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
use Victoire\Bundle\PageBundle\WidgetMap\WidgetMapBuilder;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use AppVentus\Awesome\ShortcutsBundle\Service\FormErrorService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Generic Widget CRUD operations
 */
class WidgetManager
{
    protected $container;
    protected $widget;
    protected $page;
    protected $widgetMapBuilder = null;
    protected $formErrorService = null;

    /**
     * contructor
     * @param Container        $container
     * @param WidgetMapBuilder $widgetMapBuilder
     * @param FormErrorService $formErrorService
     */
    public function __construct($container, WidgetMapBuilder $widgetMapBuilder, FormErrorService $formErrorService)
    {
        $this->container = $container;
        $this->widgetMapBuilder = $widgetMapBuilder;
        $this->formErrorService = $formErrorService;
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
     * Remove a widget
     *
     * @param Widget $widget
     *
     * @return array The parameter for the view
     */
    public function deleteWidget(Widget $widget)
    {
        //services
        $em = $this->container->get('doctrine')->getManager();
        $widgetMapBuilder = $this->widgetMapBuilder;

        //the widget id
        $widgetId = $widget->getId();

        //the page
        $widgetPage = $widget->getPage();

        //create a page for the business entity instance if we are currently display an instance for a business entity template
        $page = $this->duplicateTemplatePageIfPageInstance($widgetPage);

        //update the page deleting the widget
        $widgetMapBuilder->deleteWidgetFromPage($page, $widget);

        //we update the widget map of the page
        $page->updateWidgetMapBySlots();

        //the widget is removed only if the current page is the page of the widget
        if ($page === $widgetPage) {
            //we remove the widget
            $em->remove($widget);
        }

        //we update the page
        $em->persist($page);
        $em->flush();

        return array(
            "success"  => true,
            "widgetId" => $widgetId
        );
    }

    /**
     * create a widget
     * @param string $type
     * @param string $slotId
     * @param Page   $page
     * @param string $entity
     * @return template
     */
    public function createWidget($type, $slotId, BasePage $page, $entity)
    {
        //create a page for the business entity instance if we are currently display an instance for a business entity template
        $page = $this->duplicateTemplatePageIfPageInstance($page);

        $manager = $this->getManager(null, $type);

        if (method_exists($manager, 'createWidget')) {
            return $manager->createWidget($type, $slotId, $page, $entity, $this);
        }

        $widget = $manager->newWidget($page, $slotId);
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
            if ($entity) {
                $widget->setBusinessClass($classes[$entity]);
            }
            $em->persist($widget);
            $em->flush();

            //the id of the widget
            $widgetId = $widget->getId();

            //create the new widget map
            $widgetMapEntry = new WidgetMap();
            $widgetMapEntry->setAction(WidgetMap::ACTION_CREATE);
            $widgetMapEntry->setWidgetId($widgetId);

            //get the slot
            $slot = $page->getSlotById($slotId);

            //test that slot exists
            if ($slot === null) {
                throw new \Exception('The slot with the id:['.$slotId.'] was not found for the widget with the id:['.$widgetId.']');
            }

            //update the slot
            $slot->addWidgetMap($widgetMapEntry);

            //update the widget map
            $page->updateWidgetMapBySlots();

            $em->persist($page);
            $em->flush();

            //get the html for the widget
            $hmltWidget = $this->render($widget, $page, true);

            return array(
                "success" => true,
                "html"    => $hmltWidget
            );
        }


        $forms = $this->renderNewWidgetForms($entity, $slotId, $page, $widget);

        return array(
            "success" => false,
            "html"    => $this->container->get('victoire_templating')->render(
                "VictoireCoreBundle:Widget:new.html.twig",
                array(
                    'page'    => $page,
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
                    'page'    => $page,
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
    public function edit(Request $request, Widget $widget, $entity = null)
    {
        //services
        $widgetMapBuilder = $this->widgetMapBuilder;

        $classes = $this->container->get('victoire_core.annotation_reader')->getBusinessClassesForWidget($widget);
        $manager = $this->getManager($widget);
        $page = $widget->getPage();

        //create a page for the business entity instance if we are currently display an instance for a business entity template
        $page = $this->duplicateTemplatePageIfPageInstance($page);

        if (method_exists($manager, 'edit')) {
            return $manager->edit($widget, $entity, $this);
        }

        //the type of method used
        $requestMethod = $request->getMethod();

        //if the form is posted
        if ($requestMethod === 'POST') {
            //
            $widget = $widgetMapBuilder->editWidgetFromPage($page, $widget);

            $form = $this->buildForm($manager, $widget);

            if ($entity) {
                $form = $this->buildForm($manager, $widget, $entity, $classes[$entity]);
            }

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->container->get('doctrine')->getManager();

                $widget->setBusinessEntityName($entity);

                $em->persist($widget);

                //update the widget map by the slots
                $page->updateWidgetMapBySlots();
                $em->persist($page);
                $em->flush();

                $response = array(
                    'page'     => $page,
                    'success'   => true,
                    'html'     => $this->render($widget),
                    'widgetId' => "vic-widget-".$widget->getId()."-container"
                );
            } else {
                $formErrorService = $this->formErrorService;

                $errors = $formErrorService->getRecursiveReadableErrors($form);

                $response =  array(
                    'success' => false,
                    'message' => $errors
                );
            }
        } else {
            $forms = $this->renderWidgetForms($widget);

            $response = array(
                "success"  => false,
                "html"     => $this->container->get('victoire_templating')->render(
                    "VictoireCoreBundle:Widget:Form/edit.html.twig",
                    array(
                        'page'    => $page,
                        'classes' => $classes,
                        'forms'   => $forms,
                        'widget'  => $widget
                    )
                )
            );
        }

        return $response;
    }

    /**
     * render a widget
     *
     * @param Widget  $widget
     * @param boolean $addContainer
     *
     * @return template
     */
    public function render(Widget $widget, $addContainer = false)
    {
        $widgetManager = $this->getManager($widget);

        //the widget should all extends BaseWidgetManager
        if (method_exists($widgetManager, 'renderContainer')) {
            $html = $widgetManager->renderContainer($widget, $addContainer);
        } else {
            //but in order to keep retro compatibility
            //we test if the method exists
            $html = $widgetManager->render($widget);
        }

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
     * compute the widget map for page
     * @param BasePage   $page
     * @param array      $sortedWidgets
     */
    public function updateWidgetMapOrder(BasePage $page, $sortedWidgets)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $widgetMapBuilder = $this->widgetMapBuilder;

        $widgetMap = array();
        $widgetSlots = array();

        //parse the sorted widgets
        foreach ($sortedWidgets as $slot => $widgetContainers) {
            //get the slot id removing the prefix
            $slotId = str_replace('vic-slot-', '', $slot);

            //create an array for this slot
            $widgetSlots[$slotId] = array();

            //parse the list of div ids
            foreach ($widgetContainers as $containerId) {
                //get the widget id from the div id  (remove the text around non numerical characters)
                $widgetId = preg_replace('/[^0-9]*/', '', $containerId);

                if ($widgetId === '' || $widgetId === null) {
                    throw new \Exception('The containerId does not have any numerical characters. Containerid:['.$containerId.']');
                }

                //test if the widget is allowed for the slot
                //@todo
//                 $isAllowed = $this->isWidgetAllowedForSlot($widget, $widgetSlots[$id]);
//                 if (!$isAllowed) {
//                     throw new \Exception('This widget is not allowed in this slot');
//                 }

                //add the id of the widget to the slot
                //cast the id as integer
                $widgetSlots[$slotId][] = intval($widgetId);
            }
        }

        zdebug($widgetSlots);
        $widgetMapBuilder->updateWidgetMapsByPage($page, $widgetSlots);

        $page->updateWidgetMapBySlots();

        //update the page with the new widget map
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

            return (
                array_key_exists($widgetName, $slots[$slot]['widgets']) &&
                $slots[$slot]['widgets'][$widgetName] == null) ||
                !array_key_exists('themes', $slots[$slot]['widgets'][$widgetName]) ||
                in_array($widgetType, $slots[$slot]['widgets'][$widgetName]['themes']);
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

    /**
     * Get the extra classes for the css
     *
     * @param Widget $widget
     *
     * @return string the extra classes
     */
    public function getExtraCssClass(Widget $widget)
    {
        $extraClasses = '';

        $manager = $this->getManager($widget);

        //if there is a manager
        if ($manager !== null) {
            //and this one has the function
            $extraClasses = $manager->getExtraCssClass();
        }

        return $extraClasses;
    }

    /**
     * If the current page is a business entity template and where are displaying an instance
     * We create a new page for this instance
     *
     * @param Page $widgetPage The page of the widget
     *
     * @return Page The page for the entity instance
     */
    public function duplicateTemplatePageIfPageInstance(Page $page)
    {
        //we copy the reference to the widget page
        $widgetPage = $page;

        //services
        $pageHelper = $this->container->get('victoire_page.page_helper');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $urlHelper = $this->container->get('victoire_page.url_helper');

        //if the url of the referer is not the same as the url of the page of the widget
        //it means we are in a business entity template page and displaying an instance
        $url = $urlHelper->getAjaxUrlRefererWithoutBase();
        $widgetPageUrl = $widgetPage->getUrl();

        //the widget is linked to a page url that is not the current page url
        if ($url !== $widgetPageUrl) {
            //we try to get the page if it exists
            $basePageRepository = $em->getRepository('VictoirePageBundle:BasePage');

            //the url for the new page
            $newPageUrl = $urlHelper->getEntityIdFromUrl($url);

            //get the page
            $page = $basePageRepository->findOneByUrl($url);

            //no page were found
            if ($page === null) {
                //so we duplicate the business entity template page for this current instance
                $page = $pageHelper->createPageInstanceFromBusinessEntityTemplatePage($widgetPage, $newPageUrl);

                //the page
                $em->persist($page);
            }
        }

        return $page;
    }
}
