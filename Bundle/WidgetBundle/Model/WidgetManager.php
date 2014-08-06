<?php

namespace Victoire\Bundle\WidgetBundle\Model;

use AppVentus\Awesome\ShortcutsBundle\Service\FormErrorService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\CoreBundle\Annotations\Reader\AnnotationReader;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Template\TemplateMapper;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Victoire\Bundle\PageBundle\WidgetMap\WidgetMapBuilder;
use Victoire\Bundle\WidgetBundle\Builder\WidgetFormBuilder;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;
use Victoire\Bundle\WidgetBundle\Renderer\WidgetRenderer;
use Victoire\Bundle\WidgetBundle\Resolver\WidgetContentResolver;

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
    protected $widgetMapBuilder;
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
        $this->widgetMapBuilder = $widgetMapBuilder;
        $this->annotationReader = $annotationReader;
        $this->victoireTemplating = $victoireTemplating;
        $this->pageHelper = $pageHelper;
        $this->slots = $slots;
        $this->container = $container;
    }

    /**
     * new widget
     * @param string $type
     * @param string $slot
     * @param View   $view
     *
     * @return template
     */
    public function newWidget($type, $slot, View $view)
    {
        $widget = $this->widgetHelper->newWidgetInstance($type, $view, $slot);

        $classes = $this->annotationReader->getBusinessClassesForWidget($widget);
        $forms = $this->widgetFormBuilder->renderNewWidgetForms($slot, $view, $widget);

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
     *
     * @param string $type
     * @param string $slotId
     * @param View   $view
     * @param string $entity
     *
     * @return template
     *
     * @throws \Exception
     */
    public function createWidget($type, $slotId, View $view, $entity)
    {
        //services
        $formErrorService = $this->formErrorService;
        $em = $this->em;
        $request = $this->request;

        //the default response
        $response = array(
            "success" => false,
            "html"    => ''
        );

        //create a new widget
        $widget = $this->widgetHelper->newWidgetInstance($type, $view, $slotId);

        $form = $this->widgetFormBuilder->callBuildFormSwitchParameters($widget, $view, $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $view = $this->pageHelper->duplicatePagePatternIfPageInstance($view);

            //get the widget from the form
            $widget = $form->getData();

            //update fields of the widget
            $widget->setBusinessEntityName($entity);

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
            $slot = $view->getSlotById($slotId);

            //test that slot exists
            if ($slot === null) {
                $slot = new Slot();
                $slot->setId($slotId);
                $view->addSlot($slot);
            }

            //update the slot
            $slot->addWidgetMap($widgetMapEntry);

            //update the widget map
            $view->updateWidgetMapBySlots();

            $em->persist($view);
            $em->flush();

            //get the html for the widget
            $hmltWidget = $this->widgetRenderer->renderContainer($widget, true);

            $response = array(
                "success" => true,
                "html"    => $hmltWidget
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
     * @param string  $entityName
     *
     * @return template
     */
    public function editWidget(Request $request, Widget $widget, $entityName = null)
    {
        //services
        $widgetMapBuilder = $this->widgetMapBuilder;

        $classes = $this->annotationReader->getBusinessClassesForWidget($widget);

        $view = $widget->getPage();

        //the id of the edited widget
        //a new widget might be created in the case of a legacy
        $initialWidgetId = $widget->getId();

        //create a view for the business entity instance if we are currently display an instance for a business entity template
        $view = $this->pageHelper->duplicatePagePatternIfPageInstance($view);

        //the type of method used
        $requestMethod = $request->getMethod();

        //if the form is posted
        if ($requestMethod === 'POST') {

            $widget = $widgetMapBuilder->editWidgetFromPage($view, $widget);

            if ($entityName !== null) {
                $form = $this->widgetFormBuilder->buildForm($widget, $view, $entityName, $classes[$entityName]);
            } else {
                $form = $this->widgetFormBuilder->buildForm($widget, $view);
            }

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->em;

                $widget->setBusinessEntityName($entityName);

                $em->persist($widget);

                //update the widget map by the slots
                $view->updateWidgetMapBySlots();
                $em->persist($view);
                $em->flush();

                $response = array(
                    'view'     => $view,
                    'success'  => true,
                    'html'     => $this->widgetRenderer->render($widget, $entityName),
                    'widgetId' => "vic-widget-".$initialWidgetId."-container"
                );
            } else {
                $formErrorService = $this->formErrorService;
                //Return a message for developer in console and form view in order to refresh view and show form errors
                $response = array(
                    "success"   => false,
                    "message"   => $formErrorService->getRecursiveReadableErrors($form),
                    "html"      => $this->widgetRenderer->renderForm($form, $widget, $entityName)
                );

            }
        } else {
            $forms = $this->widgetFormBuilder->renderNewWidgetForms($widget->getSlot(), $view, $widget);

            $response = array(
                "success"  => true,
                "html"     => $this->victoireTemplating->render(
                    "VictoireCoreBundle:Widget:Form/edit.html.twig",
                    array(
                        'view'    => $view,
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
    public function deleteWidget(Widget $widget)
    {
        //services
        $em = $this->em;
        $widgetMapBuilder = $this->widgetMapBuilder;

        //the widget id
        $widgetId = $widget->getId();

        //the view
        $widgetPage = $widget->getPage();

        //create a view for the business entity instance if we are currently display an instance for a business entity template
        $view = $this->pageHelper->duplicatePagePatternIfPageInstance($widgetPage);

        //update the view deleting the widget
        $widgetMapBuilder->deleteWidgetFromPage($view, $widget);

        //we update the widget map of the view
        $view->updateWidgetMapBySlots();

        //the widget is removed only if the current view is the view of the widget
        if ($view === $widgetPage) {
            //we remove the widget
            $em->remove($widget);
        }

        //we update the view
        $em->persist($view);
        $em->flush();

        return array(
            "success"  => true,
            "widgetId" => $widgetId
        );
    }

}
