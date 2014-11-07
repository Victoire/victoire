<?php

namespace Victoire\Bundle\WidgetBundle\Model;

use AppVentus\Awesome\ShortcutsBundle\Service\FormErrorService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\CoreBundle\Annotations\Reader\AnnotationReader;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Template\TemplateMapper;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\WidgetBundle\Builder\WidgetFormBuilder;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;
use Victoire\Bundle\WidgetBundle\Renderer\WidgetRenderer;
use Victoire\Bundle\WidgetBundle\Resolver\WidgetContentResolver;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;
use Victoire\Bundle\WidgetMapBundle\Helper\WidgetMapHelper;
use Victoire\Bundle\WidgetMapBundle\Manager\WidgetMapManager;

/**
* This manager handles crud operations on a Widget
*/
class WidgetManager
{
    protected $widgetFormBuilder;
    protected $widgetHelper;
    protected $widgetContentResolver;
    protected $em;
    protected $formErrorService; // @av.form_error_service
    protected $request; // @request
    protected $widgetMapManager;
    protected $annotationReader;
    protected $victoireTemplating;
    protected $pageHelper;
    protected $slots; // %victoire_core.slots%
    protected $container; // @todo: remove the container di

    /**
     * construct
     * @param WidgetHelper          $widgetHelper
     * @param WidgetFormBuilder     $widgetFormBuilder
     * @param WidgetContentResolver $widgetContentResolver
     * @param WidgetRenderer        $widgetRenderer
     * @param EntityManager         $em
     * @param FormErrorService      $formErrorService
     * @param Request               $request
     * @param WidgetMapManager      $widgetMapManager
     * @param WidgetMapHelper       $widgetMapHelper
     * @param WidgetMapBuilder      $widgetMapBuilder
     * @param AnnotationReader      $annotationReader
     * @param TemplateMapper        $victoireTemplating
     * @param PageHelper            $pageHelper
     * @param array                 $slots
     * @param ServiceContainer      $container
     */
    public function __construct(
        WidgetHelper $widgetHelper,
        WidgetFormBuilder $widgetFormBuilder,
        WidgetContentResolver $widgetContentResolver,
        WidgetRenderer $widgetRenderer,
        EntityManager $em,
        FormErrorService $formErrorService,
        Request $request,
        WidgetMapManager $widgetMapManager,
        WidgetMapHelper $widgetMapHelper,
        WidgetMapBuilder $widgetMapBuilder,
        AnnotationReader $annotationReader,
        TemplateMapper $victoireTemplating,
        PageHelper $pageHelper,
        $slots,
        Container $container
    )
    {
        $this->widgetFormBuilder = $widgetFormBuilder;
        $this->widgetHelper = $widgetHelper;
        $this->widgetContentResolver = $widgetContentResolver;
        $this->widgetRenderer = $widgetRenderer;
        $this->em = $em;
        $this->formErrorService = $formErrorService;
        $this->request = $request;
        $this->widgetMapManager = $widgetMapManager;
        $this->widgetMapHelper = $widgetMapHelper;
        $this->widgetMapBuilder = $widgetMapBuilder;
        $this->annotationReader = $annotationReader;
        $this->victoireTemplating = $victoireTemplating;
        $this->pageHelper = $pageHelper;
        $this->slots = $slots;
        $this->container = $container;
    }

    /**
     * new widget
     * @param string  $type
     * @param string  $slot
     * @param View    $view
     * @param integer $position
     *
     * @return template
     */
    public function newWidget($type, $slot, $view, $position)
    {
        $widget = $this->widgetHelper->newWidgetInstance($type, $view, $slot);

        $classes = $this->annotationReader->getBusinessClassesForWidget($widget);
        $forms = $this->widgetFormBuilder->renderNewWidgetForms($slot, $view, $widget, $position);

        return array(
            "html" => $this->victoireTemplating->render(
                "VictoireCoreBundle:Widget:Form/new.html.twig",
                array(
                    'view'    => $view,
                    'classes' => $classes,
                    'widget'  => $widget,
                    'forms'   => $forms
                )
            )
        );
    }

    /**
     * Create a widget
     * @param string  $type
     * @param string  $slotId
     * @param View    $view
     * @param string  $entity
     * @param integer $positionReference
     *
     * @return template
     *
     * @throws \Exception
     */
    public function createWidget($type, $slotId, View $view, $entity, $positionReference)
    {
        //services
        $formErrorService = $this->formErrorService;
        $request = $this->request;

        //the default response
        $response = array(
            "success" => false,
            "html"    => ''
        );

        //create a new widget
        $widget = $this->widgetHelper->newWidgetInstance($type, $view, $slotId);

        $form = $this->widgetFormBuilder->callBuildFormSwitchParameters($widget, $view, $entity, $positionReference);

        $form->handleRequest($request);
        if ($form->isValid()) {

            if (!$view->getId()) {
                //create a view for the business entity instance if we are currently on a virtual one
                $this->em->persist($view);
            }

            //get the widget from the form
            $widget = $form->getData();

            //update fields of the widget
            $widget->setBusinessEntityName($entity);

            //persist the widget
            $this->em->persist($widget);
            $this->em->flush();

            //create the new widget map
            $widgetMapEntry = new WidgetMap();
            $widgetMapEntry->setAction(WidgetMap::ACTION_CREATE);
            $widgetMapEntry->setWidgetId($widget->getId());

            $widgetMap = $this->widgetMapBuilder->build($view, false);

            $widgetMapEntry = $this->widgetMapHelper->generateWidgetPosition($widgetMapEntry, $widget, $widgetMap, $positionReference);
            $this->widgetMapHelper->insertWidgetMapInSlot($slotId, $widgetMapEntry, $view);

            $this->em->persist($view);
            $this->em->flush();

            $widget->setCurrentView($view);

            //get the html for the widget
            $hmltWidget = $this->widgetRenderer->renderContainer($widget, $view, $widgetMapEntry->getPosition());

            $response = array(
                "success"  => true,
                "widgetId" => "vic-widget-".$widget->getId()."-container",
                "html"     => $hmltWidget
            );
        } else {
            //get the errors as a string
            $response = array(
                "success" => false,
                "message" => $formErrorService->getRecursiveReadableErrors($form),
                "html"    => $this->widgetFormBuilder->renderNewForm($form, $widget, $slotId, $view, $entity)
            );

        }

        return $response;
    }

    /**
     * edit a widget
     *
     * @param Request $request
     * @param Widget  $widget
     * @param View    $currentView
     * @param string  $entityName  The entity name is used to know which form to submit
     *
     * @return template
     */
    public function editWidget(Request $request, Widget $widget, View $currentView, $entityName = null)
    {

        $classes = $this->annotationReader->getBusinessClassesForWidget($widget);

        $widget->setCurrentView($currentView);

        //the id of the edited widget
        //a new widget might be created in the case of a legacy
        $initialWidgetId = $widget->getId();

        //the type of method used
        $requestMethod = $request->getMethod();

        //if the form is posted
        if ($requestMethod === 'POST') {

            //the widget view
            $widgetView = $widget->getView();

            //we only copy the widget if the view of the widget is not the current view
            if ($widgetView !== $currentView) {
                $widget = $this->overwriteWidget($currentView, $widget);
            }
            if ($entityName !== null) {
                $form = $this->widgetFormBuilder->buildForm($widget, $currentView, $entityName, $classes[$entityName]);
            } else {
                $form = $this->widgetFormBuilder->buildForm($widget, $currentView);
            }

            $form->handleRequest($request);

            if ($form->isValid()) {

                $widget->setBusinessEntityName($entityName);

                $this->em->persist($widget);

                //update the widget map by the slots
                $currentView->updateWidgetMapBySlots();
                $this->em->persist($currentView);
                $this->em->flush();

                $response = array(
                    'view'        => $currentView,
                    'success'     => true,
                    'html'        => $this->widgetRenderer->render($widget, $currentView),
                    'widgetId'    => "vic-widget-".$initialWidgetId."-container"
                );
            } else {
                $formErrorService = $this->formErrorService;
                //Return a message for developer in console and form view in order to refresh view and show form errors
                $response = array(
                    "success" => false,
                    "message" => $formErrorService->getRecursiveReadableErrors($form),
                    "html"    => $this->widgetFormBuilder->renderForm($form, $widget, $entityName)
                );

            }
        } else {
            $forms = $this->widgetFormBuilder->renderNewWidgetForms($widget->getSlot(), $currentView, $widget);

            $response = array(
                "success"  => true,
                "html"     => $this->victoireTemplating->render(
                    "VictoireCoreBundle:Widget:Form/edit.html.twig",
                    array(
                        'view'    => $currentView,
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
     * Remove a widget
     *
     * @param Widget $widget
     *
     * @return array The parameter for the view
     */
    public function deleteWidget(Widget $widget, View $view)
    {
        //update the view deleting the widget
        $this->widgetMapManager->deleteWidgetFromView($view, $widget);

        //we update the widget map of the view
        $view->updateWidgetMapBySlots();
        //Used to update view in callback (we do it before delete it else it'll not exists anymore)
        $widgetId = $widget->getId();
        //the widget is removed only if the current view is the view of the widget
        if ($view === $widget->getView()) {
            //we remove the widget
            $this->em->remove($widget);
        }

        //we update the view
        $this->em->persist($view);
        $this->em->flush();

        return array(
            "success"  => true,
            "widgetId" => $widgetId
        );
    }

    /**
     * Overwrite the widget for the current view because the widget is not linked to the current view, a copy is created
     *
     * @param View   $view
     * @param Widget $widget
     *
     * @return Widget The widget
     *
     * @throws \Exception The slot does not exists
     */
    public function overwriteWidget(View $view, Widget $widget)
    {
        $widgetCopy = clone $widget;
        $widgetCopy->setView($view);

        //Look for on_to_many relations, if found, duplicate related entities.
        //It is necessary for 'list' widgets, this algo duplicates and persists list items.
        $associations = $this->em->getClassMetadata(get_class($widget))->getAssociationMappings();
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($associations as $name => $values) {
            if ($values['type'] === ClassMetadataInfo::ONE_TO_MANY) {
                $relatedEntities = $accessor->getValue($widget, $values['fieldName']);
                $relatedEntitiesCopies = array();
                foreach ($relatedEntities as $relatedEntity) {
                    $relatedEntityCopy = clone $relatedEntity;
                    $this->em->persist($relatedEntity);
                    $relatedEntitiesCopies[] = $relatedEntityCopy;
                }
                $accessor->setValue($widgetCopy, $name, $relatedEntitiesCopies);
            }
        }

        //we have to persist the widget to get its id
        $this->em->persist($view);
        $this->em->persist($widgetCopy);
        $this->em->flush();

        $this->widgetMapManager->overwriteWidgetMap($widgetCopy, $widget, $slot, $view);

        return $widgetCopy;
    }

}
