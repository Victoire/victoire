<?php
namespace Victoire\Bundle\WidgetBundle\Builder;

use Symfony\Component\DependencyInjection\Container;
use Victoire\Bundle\CoreBundle\Event\WidgetBuildFormEvent;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\WidgetBundle\Model\Widget;

class WidgetFormBuilder
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    /**
     * create a form with given widget
     *
     * @param WidgetRedactor $widget     the widget
     * @param Page           $page       the page
     * @param string         $entityName the entity class
     * @param string         $namespace  the namespace
     * @param string         $formMode   the form mode
     *
     * @return $form
     *
     * @throws \Exception
     */
    public function buildForm($widget, Page $page, $entityName = null, $namespace = null, $formMode = Widget::MODE_STATIC)
    {
        //test parameters
        if ($entityName !== null) {
            if ($namespace === null) {
                throw new \Exception('The namespace is mandatory if the entityName is given');
            }
            if ($formMode === null) {
                throw new \Exception('The formMode is mandatory if the entityName is given');
            }
        }

        $form = $this->buildWidgetForm($widget, $page, $entityName, $namespace, $formMode);

        //send event
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_BUILD_FORM, new WidgetBuildFormEvent($widget, $form));

        return $form;
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
    public function renderNewForm($form, $widget, $slot, Page $page, $entity = null)
    {
        $router = $this->container->get('router');

        //are we updating or creating the widget?
        if ($widget->getId() === null) {
            $action = 'new';
        } else {
            $action = 'edit';
        }

        //the template displayed is in the widget bundle
        $templateName = $this->container->get('victoire_widget.widget_helper')->getTemplateName($action, $widget);

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
     * render WidgetRedactor form
     * @param Form           $form
     * @param WidgetRedactor $widget
     * @param BusinessEntity $entity
     *
     * @return form
     */
    public function renderForm($form, $widget, $entity = null)
    {
        //the template displayed is in the widget bundle
        $templateName = $this->container->get('widget_helper')->getTemplateName('edit', $widget);

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
     * @todo should reside in a WidgetFormBuilder class
     * Generates new forms for each available business entities
     *
     * @param string $slot
     * @param Page   $page
     * @param Widget $widget
     *
     * @return collection of forms
     */
    public function renderNewWidgetForms($slot, Page $page, Widget $widget)
    {
        $annotationReader = $this->container->get('victoire_core.annotation_reader');
        $classes = $annotationReader->getBusinessClassesForWidget($widget);
        $manager = $this->container->get('widget_manager');

        //the static form
        $forms['static'] = array();
        $forms['static']['main'] = $this->renderNewForm($this->buildForm($widget, $page), $widget, $slot, $page);

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
     * @todo should reside in a WidgetFormBuilder class
     * @param unknown $manager
     * @param unknown $widget
     * @param Page    $page
     * @param string  $entityName
     * @param string  $namespace
     *
     * @return multitype:\Victoire\Bundle\CoreBundle\Widget\Managers\Form
     */
    public function buildEntityForms($manager, $widget, Page $page, $entityName = null, $namespace = null)
    {
        $forms = array();

        //get the entity form
        $entityForm = $this->buildForm($widget, $page, $entityName, $namespace, Widget::MODE_ENTITY);
        $forms[Widget::MODE_ENTITY] = $entityForm;

        //get the query form
        $queryForm = $this->buildForm($widget, $page, $entityName, $namespace, Widget::MODE_QUERY);
        $forms[Widget::MODE_QUERY] = $queryForm;

        //get the query form
        $businessEntityForm = $this->buildForm($widget, $page, $entityName, $namespace, Widget::MODE_BUSINESS_ENTITY);
        $forms[Widget::MODE_BUSINESS_ENTITY] = $businessEntityForm;

        return $forms;
    }

    /**
     * create a form with given widget
     *
     * @param WidgetRedactor $widget
     * @param Page           $page
     * @param string         $entityName
     * @param string         $namespace
     * @param string         $formMode
     *
     * @return $form
     *
     * @throws \Exception
     */
    public function buildWidgetForm(Widget $widget, Page $page, $entityName = null, $namespace = null, $formMode = null)
    {
        $router = $this->container->get('router');

        //test parameters
        if ($entityName !== null) {
            if ($namespace === null) {
                throw new \Exception('The namespace is mandatory if the entityName is given');
            }
            if ($formMode === null) {
                throw new \Exception('The formMode is mandatory if the entityName is given');
            }
        }

        $container = $this->container;
        $formFactory = $container->get('form.factory');

        $formAlias = 'victoire_widget_form_'.strtolower($this->container->get('victoire_widget.widget_helper')->getWidgetName($widget));

        //are we updating or creating the widget?
        if ($widget->getId() === null) {
            $formUrl = $router->generate('victoire_core_widget_create',
                array(
                    'page' => $page->getId(),
                    'slot' => $widget->getSlot(),
                    'type' => $widget->getType(),
                    'entity' => $entityName
                )
            );
        } else {
            $formUrl = $router->generate('victoire_core_widget_update',
                array(
                    'id' => $widget->getId(),
                    'type' => $entityName
                )
            );
        }

        $form = $formFactory->create($formAlias, $widget,
            array(
                'entityName' => $entityName,
                'namespace' => $namespace,
                'mode' => $formMode,
                'action'  => $formUrl,
                'method' => 'POST'
            )
        );

        return $form;
    }

    /**
     * @todo should reside in a WidgetFormBuilder class
     * Call the build form with selected parameter switch the parameters
     * The call is not the same if an entity is provided or not
     *
     * @param Widget $widget
     * @param Page   $page
     * @param string $entityName
     *
     * @throws \Exception
     * @return \Victoire\Bundle\CoreBundle\Widget\Managers\Form
     */
    public function callBuildFormSwitchParameters(Widget $widget, $page, $entityName)
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

            $form = $this->buildForm($widget, $page, $entityName, $entityClass);
        } else {
            //build a form only with the widget
            $form = $this->buildForm($widget, $page);
        }

        return $form;
    }
}
