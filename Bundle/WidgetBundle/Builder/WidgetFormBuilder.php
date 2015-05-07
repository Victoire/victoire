<?php
namespace Victoire\Bundle\WidgetBundle\Builder;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Form;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Event\WidgetBuildFormEvent;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\WidgetBundle\Model\Widget;

class WidgetFormBuilder
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * create form new for a widget
     * @param Form    $form
     * @param Widget  $widget
     * @param string  $slot
     * @param View    $view
     * @param string  $entity
     *
     * @return Template
     */
    public function renderNewForm($form, $widget, $slot, View $view, $entity = null)
    {
        //the template displayed is in the widget bundle
        $templateName = $this->container->get('victoire_widget.widget_helper')->getTemplateName('new', $widget);

        return $this->container->get('victoire_templating')->render(
            $templateName,
            array(
                "widget" => $widget,
                'form'   => $form->createView(),
                "slot"   => $slot,
                "entity" => $entity,
                "view"   => $view
            )
        );
    }

    /**
     * render Widget form
     * @param Form $form
     * @param Widget $widget
     * @param object $entity
     *
     * @return form
     */
    public function renderForm(Form $form, Widget $widget, $entity = null)
    {
        //the template displayed is in the widget bundle
        $templateName = $this->container->get('victoire_widget.widget_helper')->getTemplateName('edit', $widget);

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
     * Generates new forms for each available business entities
     *
     * @param string $slot
     * @param View   $view
     * @param Widget $widget
     *
     * @return collection of forms
     */
    public function renderNewWidgetForms($slot, View $view, Widget $widget, $position = 0)
    {
        $annotationReader = $this->container->get('victoire_core.annotation_reader');
        $classes = $annotationReader->getBusinessClassesForWidget($widget);
        $manager = $this->container->get('widget_manager');

        //the static form
        $forms['static'] = array();
        $forms['static']['main'] = $this->renderNewForm($this->buildForm($widget, $view, null, null, Widget::MODE_STATIC, $position), $widget, $slot, $view, null);

        // Build each form relative to business entities
        foreach ($classes as $entityName => $namespace) {
            //get the forms for the business entity (entity/query/businessEntity)
            $entityForms = $this->buildEntityForms($manager, $widget, $view, $entityName, $namespace);

            //the list of forms
            $forms[$entityName] = array();

            //foreach of the entity form
            foreach ($entityForms as $formMode => $entityForm) {
                //we add the form
                $forms[$entityName][$formMode] = $this->renderNewForm($entityForm, $widget, $slot, $view, $entityName);
            }
        }

        return $forms;
    }

    /**
     * @param object $manager
     * @param Widget $widget
     * @param View    $view
     * @param string  $entityName
     * @param string  $namespace
     *
     * @return array
     */
    public function buildEntityForms($manager, $widget, View $view, $entityName = null, $namespace = null, $position = 0)
    {
        $forms = array();

        //get the entity form
        $entityForm = $this->buildForm($widget, $view, $entityName, $namespace, Widget::MODE_ENTITY, $position);
        $forms[Widget::MODE_ENTITY] = $entityForm;

        //get the query form
        $queryForm = $this->buildForm($widget, $view, $entityName, $namespace, Widget::MODE_QUERY, $position);
        $forms[Widget::MODE_QUERY] = $queryForm;

        //get the query form
        $businessEntityForm = $this->buildForm($widget, $view, $entityName, $namespace, Widget::MODE_BUSINESS_ENTITY, $position);
        $forms[Widget::MODE_BUSINESS_ENTITY] = $businessEntityForm;

        return $forms;
    }

    /**
     * create a form with given widget
     *
     * @param Widget  $widget
     * @param View    $view
     * @param string  $entityName
     * @param string  $namespace
     * @param string  $formMode
     * @param integer $position
     *
     * @return $form
     *
     * @throws \Exception
     */
    public function buildWidgetForm(Widget $widget, View $view, $entityName = null, $namespace = null, $formMode = Widget::MODE_STATIC, $position = 0)
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
        $filters = array();
        if ($this->container->has('victoire_core.filter_chain')) {
            $filters = $this->container->get('victoire_core.filter_chain')->getFilters();
        }

        //are we updating or creating the widget?
        if ($widget->getId() === null) {
            $viewReference = $view->getReference();
            $formUrl = $router->generate('victoire_core_widget_create',
                array(
                    'mode'              => $formMode,
                    'viewReference'     => $viewReference['id'],
                    'slot'              => $widget->getSlot(),
                    'type'              => $widget->getType(),
                    'entityName'        => $entityName,
                    'positionReference' => $position
                )
            );
        } else {
            $viewReference = $widget->getCurrentView()->getReference();
            $formUrl = $router->generate('victoire_core_widget_update',
                array(
                    'id'            => $widget->getId(),
                    'viewReference' => $viewReference['id'],
                    'entityName'    => $entityName
                )
            );
        }
        $params = array(
            'entityName' => $entityName,
            'namespace'  => $namespace,
            'mode'       => $formMode,
            'action'     => $formUrl,
            'method'     => 'POST',
            'filters'    => $filters,
        );

        /** @var Form $mockForm Get the base form to get the name */
        $mockForm = $formFactory->create($formAlias, $widget, $params);
        //Prefix base name with form mode to avoid to have unique form fields ids
        $form = $formFactory->createNamed(
            sprintf("%s_%s_%s", $entityName, $formMode, $mockForm->getName()),
            $formAlias,
            $widget,
            $params
        );

        return $form;
    }

    /**
     * create a form with given widget
     *
     * @param Widget  $widget     the widget
     * @param View    $view       the page
     * @param string  $entityName the entity class
     * @param string  $namespace  the namespace
     * @param string  $formMode   the form mode
     * @param integer $position
     *
     * @return $form
     *
     * @throws \Exception
     */
    public function buildForm($widget, View $view, $entityName = null, $namespace = null, $formMode = Widget::MODE_STATIC, $position = 0)
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

        $form = $this->buildWidgetForm($widget, $view, $entityName, $namespace, $formMode, $position);

        //send event
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_BUILD_FORM, new WidgetBuildFormEvent($widget, $form));

        return $form;
    }

    /**
     * Call the build form with selected parameter switch the parameters
     * The call is not the same if an entity is provided or not
     *
     * @param Widget  $widget
     * @param View    $view
     * @param string  $entityName
     * @param integer $position
     *
     * @throws \Exception
     * @return \Symfony\Component\Form\Form
     */
    public function callBuildFormSwitchParameters(Widget $widget, $view, $entityName, $position = 0)
    {
        $entityClass = null;
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
        }


        $form = $this->buildForm($widget, $view, $entityName, $entityClass, $widget->getMode(), $position);

        return $form;
    }
}
