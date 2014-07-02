<?php
namespace Victoire\Bundle\CoreBundle\Widget\Managers;

use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy;
use Victoire\Bundle\CoreBundle\Entity\Widget;
use Victoire\Bundle\CoreBundle\Entity\WidgetReference;
use Victoire\Bundle\CoreBundle\Event\WidgetBuildFormEvent;
use Victoire\Bundle\CoreBundle\Event\WidgetRenderEvent;
use Victoire\Bundle\CoreBundle\Theme\ThemeWidgetInterface;
use Victoire\Bundle\PageBundle\WidgetMap\WidgetMapBuilder;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use AppVentus\Awesome\ShortcutsBundle\Service\FormErrorService;
use Symfony\Component\HttpFoundation\Request;

use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Entity\Template;
use Victoire\Widget\MenuBundle\Entity\MenuItem;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;

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
     * Create a widget
     *
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
        $request = $this->container->get('request');

        $manager = $this->getManager(null, $type);

        return $manager->createWidget($slotId, $page, $entity);
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
    protected function renderNewWidgetForms($slot, BasePage $page, Widget $widget)
    {
        $annotationReader = $this->container->get('victoire_core.annotation_reader');
        $classes = $annotationReader->getBusinessClassesForWidget($widget);
        $manager = $this->getManager($widget);

        //the static form
        $forms['static'] = array();
        $forms['static']['main'] = $this->renderNewForm($this->buildForm($manager, $widget, $page), $widget, $slot, $page);

        // Build each form relative to business entities
        foreach ($classes as $entityName => $namespace) {
            //get the forms for the business entity (entity/query/businessEntity)
            $entityForms = $this->buildEntityForms($manager, $widget, $page, $entityName, $namespace);

            //the list of forms
            $forms[$entityName] = array();

            //foreach of the entity form
            foreach ($entityForms as $formMode => $entityForm) {
                //we add the form
                $forms[$entityName][$formMode] = $this->renderNewForm($entityForm, $widget, $slot, $page, $entityName);
            }
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
        $forms = $this->renderNewWidgetForms($slot, $page, $widget);

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

        //the id of the edited widget
        //a new widget might be created in the case of a legacy
        $initialWidgetId = $widget->getId();

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

            if ($entity !== null) {
                $form = $this->buildForm($manager, $widget, $page, $entity, $classes[$entity]);
            } else {
                $form = $this->buildForm($manager, $widget, $page);
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
                    'html'     => $this->render($widget, true),
                    'widgetId' => "vic-widget-".$initialWidgetId."-container"
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
            $forms = $this->renderNewWidgetForms($widget->getSlot(), $page, $widget);

            $response = array(
                "success"  => true,
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
        $html = '';

        $html .= $this->getManager($widget)->renderContainer($widget, $addContainer);

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
     *
     * @param widget $widget
     * @param string $type
     *
     * @return widget type
     */
    public function getWidgetType($widget, $type = null)
    {
        if ($type !== null) {
           $widgetClass = array("Widget".ucfirst($type));
        } else {
           $widgetClass = explode('\\', get_class($widget));
        }

        //the class name of the widget or theme
        $widgetName = end($widgetClass);

        //we remove the beginning Widget from the namespace
        $widgetName = preg_replace('/^Widget/', '', $widgetName);
        //or the beginning Theme if it is a theme
        $widgetName = preg_replace('/^Theme/', '', $widgetName);

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
    public function buildForm($manager, $widget, BasePage $page, $entityName = null, $namespace = null, $formMode = Widget::MODE_STATIC)
    {
        $form = $manager->buildForm($widget, $page, $entityName, $namespace, $formMode);

        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_BUILD_FORM, new WidgetBuildFormEvent($widget, $form));

        return $form;
    }

    /**
     *
     * @param unknown $manager
     * @param unknown $widget
     * @param string $entityName
     * @param string $namespace
     * @return multitype:
     */
    public function buildEntityForms($manager, $widget, BasePage $page,$entityName = null, $namespace = null)
    {
        $forms = array();

        //get the entity form
        $entityForm = $this->buildForm($manager, $widget, $page, $entityName, $namespace, Widget::MODE_ENTITY);
        $forms[Widget::MODE_ENTITY] = $entityForm;

        //get the query form
        $queryForm = $this->buildForm($manager, $widget, $page, $entityName, $namespace, Widget::MODE_QUERY);
        $forms[Widget::MODE_QUERY] = $queryForm;

        //get the query form
        $businessEntityForm = $this->buildForm($manager, $widget, $page, $entityName, $namespace, Widget::MODE_BUSINESS_ENTITY);
        $forms[Widget::MODE_BUSINESS_ENTITY] = $businessEntityForm;

        return $forms;
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
    public function renderNewForm($form, $widget, $slot, BasePage $page, $entityName = null)
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
    public function duplicateTemplatePageIfPageInstance(BasePage $page)
    {
        //we copy the reference to the widget page
        $widgetPage = $page;

        //services
        $pageHelper = $this->container->get('victoire_page.page_helper');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $urlHelper = $this->container->get('victoire_page.url_helper');
        $businessEntityHelper = $this->container->get('victoire_core.helper.business_entity_helper');

        //if the url of the referer is not the same as the url of the page of the widget
        //it means we are in a business entity template page and displaying an instance
        $url = $urlHelper->getAjaxUrlRefererWithoutBase();
        $widgetPageUrl = $widgetPage->getUrl();

        //the widget is linked to a page url that is not the current page url
        if ($url !== $widgetPageUrl) {
            //we try to get the page if it exists
            $basePageRepository = $em->getRepository('VictoirePageBundle:BasePage');

            //the url for the new page
            $entityId = $urlHelper->getEntityIdFromUrl($url);

            //get the page
            $page = $basePageRepository->findOneByUrl($url);

            //no page were found
            if ($page === null) {
                //test the entity id
                if ($entityId === null) {
                    throw new \Exception('The id could not be retrieved from the url.');
                }

                if ($widgetPage instanceof BusinessEntityTemplatePage) {

                    $entity = $businessEntityHelper->getEntityByPageAndId($widgetPage, $entityId);

                    //so we duplicate the business entity template page for this current instance
                    $page = $pageHelper->createPageInstanceFromBusinessEntityTemplatePage($widgetPage, $entityId, $entity);

                    //the page
                    $em->persist($page);
                    $em->flush();
                } else {
                    //we restore the widget page as the page
                    //we might be editing a template
                    $page = $widgetPage;
                }
            }
        }

        return $page;
    }
}
