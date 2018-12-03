<?php

namespace Victoire\Bundle\WidgetBundle\Model;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\BusinessPageBundle\Transformer\VirtualToBusinessPageTransformer;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Event\WidgetFlushedEvent;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\FormBundle\Helper\FormErrorHelper;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Victoire\Bundle\WidgetBundle\Builder\WidgetFormBuilder;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;
use Victoire\Bundle\WidgetBundle\Renderer\WidgetRenderer;
use Victoire\Bundle\WidgetBundle\Resolver\WidgetContentResolver;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetMapBundle\Manager\WidgetMapManager;

/**
 * This manager handles crud operations on a Widget.
 */
class WidgetManager
{
    protected $widgetFormBuilder;
    protected $widgetHelper;
    protected $widgetContentResolver;
    protected $widgetRenderer;
    protected $eventDispatcher;
    protected $entityManager;
    protected $formErrorHelper;
    /**
     * @var RequestStack
     */
    protected $requestStack;
    protected $widgetMapManager;
    protected $businessEntityHelper;
    protected $templating;
    protected $pageHelper;
    protected $slots;
    protected $virtualToBpTransformer;
    protected $twigResponsive;

    /**
     * construct.
     *
     * @param WidgetHelper                     $widgetHelper
     * @param WidgetFormBuilder                $widgetFormBuilder
     * @param WidgetContentResolver            $widgetContentResolver
     * @param WidgetRenderer                   $widgetRenderer
     * @param EventDispatcherInterface         $eventDispatcher
     * @param EntityManager                    $entityManager
     * @param FormErrorHelper                  $formErrorHelper
     * @param RequestStack                     $requestStack
     * @param WidgetMapManager                 $widgetMapManager
     * @param WidgetMapBuilder                 $widgetMapBuilder
     * @param EngineInterface                  $templating
     * @param PageHelper                       $pageHelper
     * @param array                            $slots
     * @param VirtualToBusinessPageTransformer $virtualToBpTransformer
     */
    public function __construct(
        WidgetHelper $widgetHelper,
        WidgetFormBuilder $widgetFormBuilder,
        WidgetContentResolver $widgetContentResolver,
        WidgetRenderer $widgetRenderer,
        EventDispatcherInterface $eventDispatcher,
        EntityManager $entityManager,
        FormErrorHelper $formErrorHelper,
        RequestStack $requestStack,
        WidgetMapManager $widgetMapManager,
        WidgetMapBuilder $widgetMapBuilder,
        BusinessEntityHelper $businessEntityHelper,
        EngineInterface $templating,
        PageHelper $pageHelper,
        $slots,
        VirtualToBusinessPageTransformer $virtualToBpTransformer,
        array $twigResponsive
    ) {
        $this->widgetHelper = $widgetHelper;
        $this->widgetFormBuilder = $widgetFormBuilder;
        $this->widgetContentResolver = $widgetContentResolver;
        $this->widgetRenderer = $widgetRenderer;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->formErrorHelper = $formErrorHelper;
        $this->requestStack = $requestStack;
        $this->widgetMapManager = $widgetMapManager;
        $this->widgetMapBuilder = $widgetMapBuilder;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->templating = $templating;
        $this->pageHelper = $pageHelper;
        $this->slots = $slots;
        $this->virtualToBpTransformer = $virtualToBpTransformer;
        $this->twigResponsive = $twigResponsive;
    }

    /**
     * new widget.
     *
     * @param string $mode
     * @param string $type
     * @param string $slot
     * @param View   $view
     * @param int    $position
     *
     * @return array
     */
    public function newWidget($mode, $type, $slot, $view, $position, $parentWidgetMap, $quantum)
    {
        $quantum = $this->widgetFormBuilder->convertToString($quantum);

        $widget = $this->widgetHelper->newWidgetInstance($type, $mode);
        $widgets = [$widget];

        /** @var BusinessEntity[] $classes */
        $classes = $this->businessEntityHelper->getAvailableForWidget($this->widgetHelper->getWidgetName($widget));

        $forms = $this->widgetFormBuilder->renderNewQuantumForms($slot, $view, $widgets, $widget, $classes, $position, $parentWidgetMap, $quantum);

        return [
            'widget' => $widget,
            'html'   => $this->templating->render(
                'VictoireCoreBundle:Widget:Form/new.html.twig',
                [
                    'id'              => time(),
                    'view'            => $view,
                    'slot'            => $slot,
                    'position'        => $position,
                    'parentWidgetMap' => $parentWidgetMap,
                    'classes'         => $classes,
                    'widgets'         => $widgets,
                    'widget'          => $widget,
                    'forms'           => $forms,
                ]
            ),
        ];
    }

    /**
     * Create a widget.
     *
     * @param string $mode
     * @param string $type
     * @param string $slotId
     * @param View   $view
     * @param string $entity
     * @param string $type
     *
     * @throws \Exception
     *
     * @return Template
     */
    public function createWidget($mode, $type, $slotId, View $view, $entity, $position, $widgetReference, $quantum)
    {
        //services
        $formErrorHelper = $this->formErrorHelper;
        $request = $this->getRequest();

        if ($view instanceof VirtualBusinessPage) {
            $this->virtualToBpTransformer->transform($view);
        }
        //create a new widget
        $widget = $this->widgetHelper->newWidgetInstance($type, $mode);

        $businessEntity = $this->entityManager->getRepository('VictoireBusinessEntityBundle:BusinessEntity')->findOneBy(['name' => $entity]);
        $form = $this->widgetFormBuilder->callBuildFormSwitchParameters($widget, $view, $businessEntity, $position, $widgetReference, $slotId, $quantum);

        $noValidate = $request->query->get('novalidate', false);

        $form->handleRequest($request);
        if ($noValidate === false && $form->isValid()) {
            if (!$view->getId()) {
                //create a view for the business entity instance if we are currently on a virtual one
                $this->entityManager->persist($view);
            }

            //get the widget from the form
            $widget = $form->getData();

            //persist the widget
            $this->entityManager->persist($widget);
            $this->entityManager->flush();

            $this->widgetMapManager->insert($widget, $view, $slotId, $position, $widgetReference);

            $this->entityManager->persist($view);
            $this->entityManager->flush();

            $widget->setCurrentView($view);

            $event = new WidgetFlushedEvent($widget);
            $this->eventDispatcher->dispatch(VictoireCmsEvents::WIDGET_POST_FLUSH, $event);
            $this->eventDispatcher->dispatch(VictoireCmsEvents::WIDGET_POST_FLUSH.'_'.strtoupper($type), $event);

            $this->widgetMapBuilder->build($view);

            //get the html for the widget
            $htmlWidget = $this->widgetRenderer->renderContainer($widget, $view);

            $response = [
                'success'  => true,
                'widgetId' => $widget->getId(),
                'html'     => $htmlWidget,
            ];
        } else {
            //get the errors as a string
            $response = [
                'success' => false,
                'message' => $noValidate === false ? $formErrorHelper->getRecursiveReadableErrors($form) : null,
                'html'    => $this->widgetFormBuilder->renderNewForm($form, $widget, $slotId, $view, $quantum, $entity),
            ];
        }

        return $response;
    }

    /**
     * Edit a widget.
     *
     * @param Widget $widget
     * @param View   $currentView
     * @param string $businessEntityId The entity name is used to know which form to submit
     *
     * @return template
     */
    public function editWidget(Widget $widget, View $currentView, $quantum = null, $businessEntityId = null, $widgetMode = Widget::MODE_STATIC)
    {
        /** @var BusinessEntity[] $classes */
        $classes = $this->businessEntityHelper->getAvailableForWidget($this->widgetHelper->getWidgetName($widget));
        $request = $this->getRequest();

        //the id of the edited widget
        //a new widget might be created in the case of a legacy
        $initialWidgetId = $widget->getId();

        //the type of method used
        $requestMethod = $request->getMethod();

        //if the form is posted
        if ($requestMethod === 'POST') {
            //the widget view
            $widgetView = $widget->getWidgetMap()->getView();

            //we only copy the widget if the view of the widget is not the current view
            if ($widgetView !== $currentView) {
                $widget = $this->overwriteWidget($currentView, $widget);
            }
            $form = $this->widgetFormBuilder->buildForm($widget, $currentView, $businessEntityName, $widgetMode, null, null, null, $quantum);

            $noValidate = $request->query->get('novalidate', false);
            $form->handleRequest($request);
            if ($noValidate === false && $form->isValid()) {

                //force cache invalidation
                $widget->setUpdatedAt(new \DateTime());
                $this->entityManager->persist($widget);

                $this->entityManager->persist($currentView);
                $this->entityManager->flush();

                $response = [
                    'view'        => $currentView,
                    'success'     => true,
                    'html'        => $this->widgetRenderer->render($widget, $currentView),
                    'widgetId'    => $initialWidgetId,
                    'slot'        => $widget->getWidgetMap()->getSlot(),
                    'viewCssHash' => $currentView->getCssHash(),
                ];
            } else {
                $formErrorHelper = $this->formErrorHelper;
                //Return a message for developer in console and form view in order to refresh view and show form errors
                $response = [
                    'success'  => false,
                    'widgetId' => $initialWidgetId,
                    'slot'     => $widget->getWidgetMap()->getSlot(),
                    'message'  => $noValidate === false ? $formErrorHelper->getRecursiveReadableErrors($form) : null,
                    'html'     => $this->widgetFormBuilder->renderForm($form, $widget, $businessEntityName),
                ];
            }
        } else {
            $widgets = $widget->getWidgetMap()->getWidgets();
            $forms = $this->widgetFormBuilder->renderNewQuantumForms($widget->getSlot(), $currentView, $widgets, $widget, $classes);

            $response = [
                'success' => true,
                'html'    => $this->templating->render(
                    'VictoireCoreBundle:Widget:Form/edit.html.twig',
                    [
                        'view'            => $currentView,
                        'slot'            => $widget->getWidgetMap()->getSlot(),
                        'position'        => $widget->getWidgetMap()->getPosition(),
                        'parentWidgetMap' => $widget->getWidgetMap()->getParent() ? $widget->getWidgetMap()->getParent()->getId() : null,
                        'classes'         => $classes,
                        'forms'           => $forms,
                        'widgets'         => $widgets,
                        'widget'          => $widget,
                    ]
                ),
            ];
        }

        return $response;
    }

    /**
     * Edit widget style.
     *
     * @param Request $request
     * @param Widget  $widget
     * @param View    $view
     * @param string  $viewReference
     * @param string  $activeQuantum
     *
     * @return JsonResponse
     */
    public function editWidgetStyle(Request $request, Widget $widget, View $view, $viewReference = null, $activeQuantum = null)
    {
        if ($request->getMethod() === 'POST') {
            $form = $this->widgetFormBuilder->buildWidgetStyleForm($widget, $viewReference, $activeQuantum);
            $form->handleRequest($request);

            if ($request->query->get('novalidate', false) === false && $form->isValid()) {
                if ($form->has('deleteBackground') && $form->get('deleteBackground')->getData()) {
                    // @todo: dynamic responsive key
                    foreach (['', 'XS', 'SM', 'MD', 'LG'] as $key) {
                        $widget->{'deleteBackground'.$key}();
                    }
                }
                $this->entityManager->flush();
                $params = [
                    'view'        => $view,
                    'success'     => true,
                    'html'        => $this->widgetRenderer->render($widget, $view),
                    'widgetId'    => $widget->getId(),
                    'viewCssHash' => $view->getCssHash(),
                ];
            } else {
                $template = ($request->query->get('novalidate', false) !== false) ? 'VictoireCoreBundle:Widget/Form/stylize:form.html.twig' : 'VictoireCoreBundle:Widget/Form:stylize.html.twig';
                $params = [
                    'success' => !$form->isSubmitted(),
                    'html'    => $this->templating->render(
                        $template,
                        [
                            'view'                     => $view,
                            'form'                     => $form->createView(),
                            'widget'                   => $widget,
                            'victoire_twig_responsive' => $this->twigResponsive,
                        ]
                    ),
                ];
            }
        } else {
            $widgets = $widget->getWidgetMap()->getWidgets();
            $forms = $this->widgetFormBuilder->renderQuantumStyleForms($viewReference, $widgets, $widget);
            $params = [
                'html' => $this->templating->render(
                    'VictoireCoreBundle:Widget/Form:stylize.html.twig',
                    [
                        'view'                     => $view,
                        'forms'                    => $forms,
                        'widget'                   => $widget,
                        'widgets'                  => $widgets,
                        'victoire_twig_responsive' => $this->twigResponsive,
                    ]
                ),
            ];
        }

        return new JsonResponse($params);
    }

    /**
     * Remove a widget.
     *
     * @param Widget $widget
     * @param View   $view
     *
     * @return void
     */
    public function deleteWidget(Widget $widget, View $view)
    {
        //we update the widget map of the view
        $this->widgetMapBuilder->build($view);
        $widgetMap = $widget->getWidgetMap();
        //the widget is removed only if the current view is the view of the widget
        if ($widgetMap->getView() == $view && $widgetMap->getAction() != WidgetMap::ACTION_DELETE) {
            //we remove the widget
            $this->entityManager->remove($widget);
        }

        //update the view deleting the widget
        $this->widgetMapManager->delete($view, $widget);

        //we update the view
        $this->entityManager->persist($view);

        $this->entityManager->flush();
    }

    /**
     * Overwrite the widget for the current view because the widget is not linked to the current view, a copy is created.
     *
     * @param View   $view
     * @param Widget $widget
     *
     * @throws \Exception The slot does not exists
     *
     * @return Widget The widget
     */
    public function overwriteWidget(View $view, Widget $widget)
    {
        $widgetCopy = $this->cloneEntity($widget);
        $originalWidgetMap = $widget->getWidgetMap();
        $this->widgetMapManager->overwrite($view, $originalWidgetMap, $widgetCopy);

        return $widgetCopy;
    }

    /**
     * @param Widget $entity
     */
    public function cloneEntity(Widget $entity)
    {
        $entityCopy = clone $entity;
        $entityCopy->setWidgetMap(null);
        //Look for on_to_many relations, if found, duplicate related entities.
        //It is necessary for 'list' widgets, this algo duplicates and persists list items.
        $associations = $this->entityManager->getClassMetadata(get_class($entityCopy))->getAssociationMappings();
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($associations as $name => $values) {
            if ($values['type'] === ClassMetadataInfo::ONE_TO_MANY) {
                $relatedEntities = $accessor->getValue($entityCopy, $values['fieldName']);
                $relatedEntitiesCopies = [];
                foreach ($relatedEntities as $relatedEntity) {
                    $relatedEntityCopy = clone $relatedEntity;
                    $this->entityManager->persist($relatedEntity);
                    $relatedEntitiesCopies[] = $relatedEntityCopy;
                }
                $accessor->setValue($entityCopy, $name, $relatedEntitiesCopies);
            }

            //Clone OneToOne relation objects
            if ($values['type'] === ClassMetadataInfo::ONE_TO_ONE) {
                $relatedEntity = $accessor->getValue($entityCopy, $values['fieldName']);
                if ($relatedEntity) {
                    $relatedEntityCopy = clone $relatedEntity;
                    $this->entityManager->persist($relatedEntity);
                    $accessor->setValue($entityCopy, $name, $relatedEntityCopy);
                }
            }
        }

        $this->entityManager->persist($entityCopy);

        return $entityCopy;
    }

    /**
     * @return Request
     */
    private function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}
