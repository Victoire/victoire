<?php

namespace Victoire\Bundle\WidgetBundle\Builder;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Form;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Event\WidgetBuildFormEvent;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Event\WidgetFormCreateEvent;
use Victoire\Bundle\WidgetBundle\Event\WidgetFormEvents;
use Victoire\Bundle\WidgetBundle\Form\WidgetOptionsContainer;

class WidgetFormBuilder
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * create form new for a widget.
     *
     * @param Form   $form
     * @param Widget $widget
     * @param string $slot
     * @param View   $view
     * @param string $entity
     *
     * @return string
     */
    public function renderNewForm($form, $widget, $slot, View $view, $quantum = null, $entity = null)
    {
        //the template displayed is in the widget bundle
        $templateName = $this->container->get('victoire_widget.widget_helper')->getTemplateName('new', $widget);

        return $this->container->get('templating')->render(
            $templateName,
            [
                'widget'    => $widget,
                'form'      => $form->createView(),
                'slot'      => $slot,
                'entity'    => $entity,
                'view'      => $view,
                'quantum'   => $quantum,
            ]
        );
    }

    /**
     * render Widget form.
     *
     * @param Form   $form
     * @param Widget $widget
     * @param object $entity
     *
     * @return form
     */
    public function renderForm(Form $form, Widget $widget, $entity = null)
    {
        //the template displayed is in the widget bundle
        $templateName = $this->container->get('victoire_widget.widget_helper')->getTemplateName('edit', $widget);

        return $this->container->get('templating')->render(
            $templateName,
            [
                'widget' => $widget,
                'slot'   => $widget->getWidgetMap()->getSlot(),
                'view'   => $this->container->get('victoire_core.current_view')->getCurrentView(),
                'form'   => $form->createView(),
                'id'     => $widget->getId(),
                'entity' => $entity,
            ]
        );
    }

    /**
     * Generates new forms for each available business entities.
     *
     * @param string           $slot
     * @param View             $view
     * @param Widget           $widget
     * @param BusinessEntity[] $classes
     * @param int              $position
     * @param null             $parentWidgetMap
     * @param int              $position
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\Form\Form[]
     */
    public function renderNewWidgetForms($slot, View $view, Widget $widget, $classes, $position, $parentWidgetMap, $quantum)
    {
        //the static form
        $forms['static'] = [];
        $forms['static']['main'] = $this->renderNewForm($this->buildForm($widget, $view, null, null, Widget::MODE_STATIC, $slot, $position, $parentWidgetMap, $quantum), $widget, $slot, $view, $quantum, null);

        // Build each form relative to business entities
        foreach ($classes as $businessEntity) {
            //get the forms for the business entity (entity/query/businessEntity)
            $entityForms = $this->buildEntityForms($widget, $view, $businessEntity->getId(), $businessEntity->getClass(), $position, $parentWidgetMap, $slot, $quantum);

            //the list of forms
            $forms[$businessEntity->getId()] = [];

            //foreach of the entity form
            foreach ($entityForms as $formMode => $entityForm) {
                //we add the form
                $forms[$businessEntity->getId()][$formMode] = $this->renderNewForm($entityForm, $widget, $slot, $view, $quantum, $businessEntity->getId());
            }
        }

        return $forms;
    }

    /**
     * Generates new forms for each available business entities.
     *
     * @param string           $slot
     * @param View             $view
     * @param Widget           $widget
     * @param BusinessEntity[] $classes
     * @param int              $position
     *
     * @throws \Exception
     *
     * @return Form[]
     */
    public function renderNewQuantumForms($slot, View $view, $widgets, $activeWidget, $classes, $position = null, $parentWidgetMap = null, $quantum = null)
    {
        $forms = [];
        foreach ($widgets as $key => $widget) {
            $quantum = $quantum ?: $key;
            $forms[$key] = $this->renderNewWidgetForms($slot, $view, $widget, $classes, $position, $parentWidgetMap, $quantum);
            $forms[$key]['quantum'] = $quantum;
            if ($widget === $activeWidget) {
                $forms[$key]['active'] = true;
            }
        }

        return $forms;
    }

    /**
     * @param Widget $widget
     * @param View   $view
     * @param string $businessEntityId
     * @param string $namespace
     * @param int    $position
     * @param string $slot
     *
     * @return array
     */
    protected function buildEntityForms($widget, View $view, $businessEntityId = null, $namespace = null, $position = null, $parentWidgetMap = null, $slot = null, $quantum = null)
    {
        $forms = [];

        //get the entity form
        $entityForm = $this->buildForm($widget, $view, $businessEntityId, $namespace, Widget::MODE_ENTITY, $slot, $position, $parentWidgetMap, $quantum);
        $forms[Widget::MODE_ENTITY] = $entityForm;

        //get the query form
        $queryForm = $this->buildForm($widget, $view, $businessEntityId, $namespace, Widget::MODE_QUERY, $slot, $position, $parentWidgetMap, $quantum);
        $forms[Widget::MODE_QUERY] = $queryForm;

        //get the query form
        $businessEntityForm = $this->buildForm($widget, $view, $businessEntityId, $namespace, Widget::MODE_BUSINESS_ENTITY, $slot, $position, $parentWidgetMap, $quantum);
        $forms[Widget::MODE_BUSINESS_ENTITY] = $businessEntityForm;

        return $forms;
    }

    /**
     * create a form with given widget.
     *
     * @param Widget $widget
     * @param View   $view
     * @param string $businessEntityId
     * @param string $namespace
     * @param string $formMode
     * @param int    $position
     *
     * @throws \Exception
     *
     * @return Form
     */
    public function buildWidgetForm(Widget $widget, View $view, $businessEntityId = null, $namespace = null, $formMode = Widget::MODE_STATIC, $position = null, $parentWidgetMap = null, $slotId = null, $quantum = null)
    {
        $router = $this->container->get('router');

        //test parameters
        if ($businessEntityId !== null) {
            if ($namespace === null) {
                throw new \Exception('The namespace is mandatory if the businessEntityId is given');
            }
            if (in_array($formMode, [Widget::MODE_STATIC, null])) {
                throw new \Exception('The formMode cannot be null or static if the businessEntityId is given');
            }
        }

        $container = $this->container;
        $formFactory = $container->get('form.factory');

        //are we updating or creating the widget?
        if ($widget->getId() === null) {
            $viewReference = $view->getReference();
            $actionParams = [
                'viewReference'      => $viewReference->getId(),
                'slot'               => $slotId,
                'type'               => $widget->getType(), // @todo: use the config
                'position'           => $position,
                'parentWidgetMap'    => $parentWidgetMap,
                'quantum'            => $quantum,
            ];
            $action = 'victoire_core_widget_create';
            if ($businessEntityId) {
                $actionParams['businessEntityId'] = $businessEntityId;
                $actionParams['mode'] = $formMode;
            } else {
                $action = 'victoire_core_widget_create_static';
            }
            $formUrl = $router->generate($action, $actionParams);
        } else {
            $viewReference = $this->container->get('victoire_core.current_view')->getCurrentView()->getReference();
            $formUrl = $router->generate('victoire_core_widget_update',
                [
                    'id'               => $widget->getId(),
                    'viewReference'    => $viewReference->getId(),
                    'businessEntityId' => $businessEntityId,
                    'mode'             => $formMode,
                    'quantum'          => $quantum,
                ]
            );
        }

        $widgetName = $this->container->get('victoire_widget.widget_helper')->getWidgetName($widget);

        $widgetFormTypeClass = ClassUtils::getClass(
            $this->container->get(
                sprintf(
                    'victoire.widget.form.%s',
                    strtolower($widgetName)
                )
            )
        );

        $optionsContainer = new WidgetOptionsContainer([
            'businessEntityId'      => $businessEntityId,
            'namespace'             => $namespace,
            'mode'                  => $formMode,
            'action'                => $formUrl,
            'method'                => 'POST',
            'dataSources'           => $this->container->get('victoire_criteria.chain.data_source_chain'),
        ]);

        $event = new WidgetFormCreateEvent($optionsContainer, $widgetFormTypeClass);
        $this->container->get('event_dispatcher')->dispatch(WidgetFormEvents::PRE_CREATE, $event);
        $this->container->get('event_dispatcher')->dispatch(WidgetFormEvents::PRE_CREATE.'_'.strtoupper($widgetName), $event);

        /** @var Form $mockForm Get the base form to get the name */
        $mockForm = $formFactory->create($widgetFormTypeClass, $widget, $optionsContainer->getOptions());
        //Prefix base name with form mode to avoid to have unique form fields ids

        $form = $formFactory->createNamed(
            sprintf('%s_%s_%s_%s', $businessEntityId, $this->convertToString($quantum), $formMode, $mockForm->getName()),
            $widgetFormTypeClass,
            $widget,
            $optionsContainer->getOptions()
        );

        return $form;
    }

    /**
     * This method converts a number to an alphabetic char.
     * If the number is > 26, convert to aa...az...zz...
     *
     * @param        $number
     * @param string $letter
     *
     * @return string
     */
    private function convertToString($number, $letter = 'a', $i = 0)
    {
        while ($i < $number) {
            $i++;
            $letter++;
        }

        return $letter;
    }

    /**
     * create a form with given widget.
     *
     * @param Widget $widget           the widget
     * @param View   $view             the page
     * @param string $businessEntityId the entity class
     * @param string $namespace        the namespace
     * @param string $formMode         the form mode
     * @param int    $position
     *
     * @throws \Exception
     *
     * @return Form
     */
    public function buildForm($widget, View $view, $businessEntityId = null, $namespace = null, $formMode = Widget::MODE_STATIC, $slotId = null, $position = null, $parentWidgetMap = null, $quantum = null)
    {
        //test parameters
        if ($businessEntityId !== null) {
            if ($namespace === null) {
                throw new \Exception('The namespace is mandatory if the businessEntityId is given');
            }
            if ($formMode === null) {
                throw new \Exception('The formMode is mandatory if the businessEntityId is given');
            }
        }

        $form = $this->buildWidgetForm($widget, $view, $businessEntityId, $namespace, $formMode, $position, $parentWidgetMap, $slotId, $quantum);

        //send event
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(VictoireCmsEvents::WIDGET_BUILD_FORM, new WidgetBuildFormEvent($widget, $form));

        return $form;
    }

    /**
     * Call the build form with selected parameter switch the parameters
     * The call is not the same if an entity is provided or not.
     *
     * @param Widget $widget
     * @param View   $view
     * @param string $businessEntityId
     * @param int    $position
     * @param string $slotId
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\Form\Form
     */
    public function callBuildFormSwitchParameters(Widget $widget, $view, $businessEntityId, $position, $parentWidgetMap, $slotId, $quantum)
    {
        $entityClass = null;
        //if there is an entity
        if ($businessEntityId) {
            //get the businessClasses for the widget
            $classes = $this->container->get('victoire_core.helper.business_entity_helper')->getBusinessClassesForWidget($widget);

            //test the result
            if (!isset($classes[$businessEntityId])) {
                throw new \Exception('The entity '.$businessEntityId.' was not found int the business classes.');
            }
            //get the class of the entity name
            $entityClass = $classes[$businessEntityId]->getClass();
        }

        $form = $this->buildForm($widget, $view, $businessEntityId, $entityClass, $widget->getMode(), $slotId, $position, $parentWidgetMap, $quantum);

        return $form;
    }
}
