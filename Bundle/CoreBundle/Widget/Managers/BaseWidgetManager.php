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
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\PageBundle\Entity\Slot;

/**
 * Generic Widget CRUD operations
 *
 * @TODO CLEAN THIS CLASS
 * IT IS A COPY PASTE FROM THE WIDGET MANAGER
 */
class BaseWidgetManager
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
     * Call the build form with selected parameter switch the parameters
     * The call is not the same if an entity is provided or not
     *
     * @param Widget $widget
     * @param Entity $entity
     *
     * @throws \Exception
     * @return \Victoire\Bundle\CoreBundle\Widget\Managers\Form
     */
    protected function callBuildFormSwitchParameters($widget, $entityName)
    {
        //if there is an entity
        if ($entityName) {
            //get the businessClasses for the widget
            $classes = $this->container->get('victoire_core.annotation_reader')->getBusinessClassesForWidget($widget);

            //test the result
            if (!isset($classes[$entityName])) {
                throw new \Exception('The entity '.$entityName.' was not found int the business classes.');
            }

            //get the class of the entity name
            $entityClass = $classes[$entityName];

            $form = $this->buildForm($widget, $entityName, $entityClass);
        } else {
            //build a form only with the widget
            $form = $this->buildForm($widget);
        }

        return $form;
    }

    /**
     * Update a widget by the entity
     *
     * @param Widget $widget
     * @param unknown $entity
     * @throws \Exception
     * @return Widget
     */
    protected function updateWidgetData(Widget $widget, $entity)
    {
        $em = $this->getEntityManager();

        $widget->setBusinessEntityName($entity);

        if ($entity) {
            $classes = $this->container->get('victoire_core.annotation_reader')->getBusinessClassesForWidget($widget);

            if (!isset($classes[$entity])) {
                throw new \Exception('The entity '.$entity.' was not found int the business classes.');
            }

            $entityClass = $classes[$entity];

            $widget->setBusinessClass($classes[$entity]);
        }

        return $widget;
    }

    /**
     * Create a widget
     *
     * @param string $type
     * @param string $slotId
     * @param Page   $page
     * @param string $entity
     * @return template
     *
     * @throws \Exception
     */
    public function createWidget($type, $slotId, BasePage $page, $entity)
    {
        //services
        $formErrorService = $this->container->get('av.form_error_service');
        $em = $this->getEntityManager();

        //the default response
        $response = array(
            "success" => false,
            "html"    => ''
        );

        //create a new widget
        $widget = $this->newWidget($page, $slotId);

        $form = $this->callBuildFormSwitchParameters($widget, $entity);

        $request = $this->container->get('request');
        $form->handleRequest($request);

        if ($form->isValid()) {
            //get the widget from the form
            $widget = $form->getData();

            //update fields of the widget
            $widget = $this->updateWidgetData($widget, $entity);

            //persist the widget
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
                $slot = new Slot();
                $slot->setId($slotId);
                $page->addSlot($slot);
            }

            //update the slot
            $slot->addWidgetMap($widgetMapEntry);

            //update the widget map
            $page->updateWidgetMapBySlots();

            $em->persist($page);
            $em->flush();

            //get the html for the widget
            $hmltWidget = $this->renderContainer($widget, true);

            $response = array(
                "success" => true,
                "html"    => $hmltWidget
            );
        } else {
            //get the errors as a string
            $errorMessage = $formErrorService->getRecursiveReadableErrors($form);

            throw new \Exception($errorMessage);
        }

        return $response;
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

        $forms['static'] = $this->renderNewForm($this->buildForm($widget), $widget, $slot, $page);

        // Build each form relative to business entities
        foreach ($classes as $entityName => $namespace) {
            $form = $this->buildForm($widget, $entityName, $namespace);
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

            return (
                array_key_exists($widgetName, $slots[$slot]['widgets']) &&
                $slots[$slot]['widgets'][$widgetName] == null) ||
                !array_key_exists('themes', $slots[$slot]['widgets'][$widgetName]) ||
                in_array($widgetType, $slots[$slot]['widgets'][$widgetName]['themes']);
        }


        return !empty($slots[$slot]) && (array_key_exists($widgetType, $slots[$slot]['widgets']));


    }


    /**
     * create a new WidgetRedactor
     * @param Page   $page
     * @param string $slot
     *
     * @return $widget
     */
    public function newWidget($page, $slot)
    {
        $widget = $this->getNewWidgetEntity();

        $widget->setPage($page);
        $widget->setSlot($slot);

        return $widget;
    }

    /**
     * Get the entity manager
     *
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        $em = $this->container->get('doctrine')->getManager();

        return $em;
    }

    /**
     * Get the content of an attribute of an entity given
     *
     * @param entity $entity
     * @param strin $functionName
     *
     * @return mixed
     */
    protected function getEntityAttributeValue($entity, $field)
    {
        $functionName = 'get'.ucfirst($field);

        $fieldValue = call_user_func(array($entity, $functionName));

        return $fieldValue;
    }

    /**
     * render a widget
     * @param Widget $widget
     * @return template
     */
    public function renderContainer(Widget $widget, $addContainer = false)
    {
        $html = '';
        $dispatcher = $this->container->get('event_dispatcher');
        $securityContext = $this->container->get('security.context');

        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_PRE_RENDER, new WidgetRenderEvent($widget, $html));

        $html .= $this->render($widget);

        if ($securityContext->isGranted('ROLE_VICTOIRE')) {
            $html .= $this->renderActions($widget->getSlot(), $widget->getPage());
        }

        if ($addContainer) {
            $html = "<div class='widget-container' id='vic-widget-".$widget->getId()."-container'>".$html.'</div>';
        }

        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_POST_RENDER, new WidgetRenderEvent($widget, $html));

        return $html;
    }


    /**
     * create a form with given widget
     *
     * @param WidgetRedactor $widget
     * @param string         $entityName
     * @param string         $namespace
     * @return $form
     *
     * @throws \Exception
     */
    public function buildForm($widget, $entityName = null, $namespace = null)
    {
        //test parameters
        if ($entityName !== null) {
            if ($namespace === null) {
                throw new \Exception('The namespace is mandatory if the entityName is given');
            }
        }

        $form = $this->buildWidgetForm($widget, $entityName, $namespace);

        //send event
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_BUILD_FORM, new WidgetBuildFormEvent($widget, $form));

        return $form;
    }

    /**
     * Get a new widget entity
     *
     * @return Widget
     */
    protected function getNewWidgetEntity()
    {
        //the widget alias
        $widgetAlias = 'victoire.widget.'. $this->getWidgetName();
        $widget = $this->container->get($widgetAlias);

        return $widget;
    }

    /**
     * create form new for WidgetRedactor
     * @param Form           $form
     * @param WidgetRedactor $widget
     * @param string         $slot
     * @param Page           $page
     * @param string         $entity
     *
     * @return new form
     */
    public function renderNewForm($form, $widget, $slot, $page, $entity = null)
    {
        //the name of the bundle depends of the widget name
        $bundleName = $this->getBundleName();

        //the template displayed is in the widget bundle
        $templateName = $bundleName.'::new.html.twig';

        return $this->container->get('victoire_templating')->render(
            $templateName,
            array(
                "widget"          => $widget,
                'form'            => $form->createView(),
                "slot"            => $slot,
                "entity"          => $entity,
                "renderContainer" => true,
                "page"            => $page
            )
        );
    }

    /**
     * Get the name of the widget bundle
     *
     * @return string
     */
    protected function getBundleName()
    {
        //the name of the bundle depends of the widget name
        $bundleName = 'Victoire'.ucfirst($this->getWidgetName()).'Bundle';

        return $bundleName;
    }

    /**
     * render WidgetRedactor form
     * @param Form           $form
     * @param WidgetRedactor $widget
     * @param BusinessEntity $entity
     * @return form
     */
    protected function renderForm($form, $widget, $entity = null)
    {
        //the template displayed is in the widget bundle
        $templateName = $this->getTemplateName('edit');

        return $this->container->get('victoire_templating')->render(
            $templateName,
            array(
                "widget" => $widget,
                'form'   => $form->createView(),
                'id'     => $widget->getId(),
                'entity' => $entity
            )
        );
    }

    /**
     * Get the name of the template to display for an action
     *
     * @param string $action
     *
     * @return string
     */
    protected function getTemplateName($action)
    {
        //the name of the bundle depends of the widget name
        $bundleName = $this->getBundleName();

        //the template displayed is in the widget bundle
        $templateName = $bundleName.'::'.$action.'.html.twig';

        return $templateName;
    }

    /**
     * Get the extra classes for the css
     *
     * @return string The classes
     */
    public function getExtraCssClass()
    {
        $cssClass = 'vic-widget-'.$this->getWidgetName();

        return $cssClass;
    }

    /**
     * render the WidgetRedactor
     * @param WidgetRedactor $widget
     *
     * @return widget show
     */
    public function render(Widget $widget)
    {
        //the templating service
        $templating = $this->container->get('victoire_templating');

        //the content of the widget
        $content = $this->getWidgetContent($widget);

        //the template displayed is in the widget bundle
        $templateName = $this->getTemplateName('show');

        return $templating->render(
            $templateName,
            array(
                "widget" => $widget,
                "content" => $content
            )
        );
    }

    /**
     * create a form with given widget
     * @param WidgetRedactor $widget
     * @param string         $entityName
     * @param string         $namespace
     *
     * @return $form
     *
     * @throws \Exception
     */
    public function buildWidgetForm($widget, $entityName = null, $namespace = null)
    {
        //test parameters
        if ($entityName !== null) {
            if ($namespace === null) {
                throw new \Exception('The namespace is mandatory if the entityName is given');
            }
        }

        $container = $this->container;
        $formFactory = $container->get('form.factory');

        $formAlias = 'victoire_widget_form_'.$this->getWidgetName();

        $form = $formFactory->create($formAlias, $widget,
            array(
                'entityName' => $entityName,
                'namespace' => $namespace
            )
        );

        return $form;
    }


    /**
     * Get content for the widget
     *
     * @param Widget $widget
     * @throws \Exception
     * @return Ambigous <string, unknown, \Victoire\Bundle\CoreBundle\Widget\Managers\mixed, mixed>
     */
    protected function getWidgetContent(Widget $widget)
    {
        //the mode of display of the widget
        $mode = $widget->getMode();

        //the widget must have a mode
        if ($mode === null) {
            throw new \Exception('The widget ['.$widget->getId().'] has no mode.');
        }

        //the content of the widget
        $content = '';

        switch ($mode) {
            case Widget::MODE_STATIC:
                $content = $this->getWidgetStaticContent($widget);
                break;
            case Widget::MODE_ENTITY:
                //get the content of the widget with its entity
                $content = $this->getWidgetEntityContent($widget);
                break;
            case Widget::MODE_BUSINESS_ENTITY:
                //get the content of the widget with its entity
                $content = $this->getWidgetBusinessEntityContent($widget);
                break;
            case Widget::MODE_QUERY:
                $content = $this->getWidgetQueryContent($widget);
                break;
            default:
                throw new \Exception('The mode ['.$mode.'] is not supported by the widget manager. Widget ID:['.$widget->getId().']');
        }

        return $content;
    }

    /**
     * Get the static content of the widget
     *
     * @param Widget $widget
     * @return string The static content
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    protected function getWidgetStaticContent(Widget $widget)
    {
        return '';
    }

    /**
     * Get the business entity content
     * @param Widget $widget
     * @return Ambigous <string, unknown, \Victoire\Bundle\CoreBundle\Widget\Managers\mixed, mixed>
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    protected function getWidgetBusinessEntityContent(Widget $widget)
    {
        return '';
    }

    /**
     * Get the content of the widget by the entity linked to it
     *
     * @param Widget $widget
     *
     * @return string
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    protected function getWidgetEntityContent(Widget $widget)
    {
        return '';
    }

    /**
     * Get the content of the widget for the query mode
     *
     * @param Widget $widget
     *
     * @return string
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    protected function getWidgetQueryContent(Widget $widget)
    {
        return '';
    }
}
