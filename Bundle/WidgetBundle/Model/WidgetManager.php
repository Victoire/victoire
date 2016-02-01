<?php

namespace Victoire\Bundle\WidgetBundle\Model;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Reader\BusinessEntityCacheReader;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\BusinessPageBundle\Transformer\VirtualToBusinessPageTransformer;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Template\TemplateMapper;
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
    protected $entityManager;
    protected $formErrorHelper; // @victoire_form.error_helper
    protected $request; // @request
    protected $widgetMapManager;
    protected $cacheReader; // @victoire_business_entity.cache_reader
    protected $victoireTemplating;
    protected $pageHelper;
    protected $slots; // %victoire_core.slots%
    protected $virtualToBpTransformer; // %victoire_core.slots%

    /**
     * construct.
     *
     * @param WidgetHelper              $widgetHelper
     * @param WidgetFormBuilder         $widgetFormBuilder
     * @param WidgetContentResolver     $widgetContentResolver
     * @param WidgetRenderer            $widgetRenderer
     * @param EntityManager             $entityManager
     * @param FormErrorHelper           $formErrorHelper
     * @param Request                   $request
     * @param WidgetMapManager          $widgetMapManager
     * @param WidgetMapBuilder          $widgetMapBuilder
     * @param BusinessEntityCacheReader $cacheReader
     * @param TemplateMapper            $victoireTemplating
     * @param PageHelper                $pageHelper
     * @param array                     $slots
     */
    public function __construct(
        WidgetHelper $widgetHelper,
        WidgetFormBuilder $widgetFormBuilder,
        WidgetContentResolver $widgetContentResolver,
        WidgetRenderer $widgetRenderer,
        EntityManager $entityManager,
        FormErrorHelper $formErrorHelper,
        Request $request,
        WidgetMapManager $widgetMapManager,
        WidgetMapBuilder $widgetMapBuilder,
        BusinessEntityCacheReader $cacheReader,
        TemplateMapper $victoireTemplating,
        PageHelper $pageHelper,
        $slots,
        VirtualToBusinessPageTransformer $virtualToBpTransformer
    ) {
        $this->widgetFormBuilder = $widgetFormBuilder;
        $this->widgetHelper = $widgetHelper;
        $this->widgetContentResolver = $widgetContentResolver;
        $this->widgetRenderer = $widgetRenderer;
        $this->entityManager = $entityManager;
        $this->formErrorHelper = $formErrorHelper;
        $this->request = $request;
        $this->widgetMapManager = $widgetMapManager;
        $this->widgetMapBuilder = $widgetMapBuilder;
        $this->cacheReader = $cacheReader;
        $this->victoireTemplating = $victoireTemplating;
        $this->pageHelper = $pageHelper;
        $this->slots = $slots;
        $this->virtualToBpTransformer = $virtualToBpTransformer;
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
    public function createWidget($mode, $type, $slotId, View $view, $entity, $position, $widgetReference)
    {
        //services
        $formErrorHelper = $this->formErrorHelper;
        $request = $this->request;

        if ($view instanceof VirtualBusinessPage) {
            $this->virtualToBpTransformer->transform($view);
        }
        //create a new widget
        $widget = $this->widgetHelper->newWidgetInstance($type, $view, $slotId, $mode);

        $form = $this->widgetFormBuilder->callBuildFormSwitchParameters($widget, $view, $entity, $position, $widgetReference, $slotId);

        $noValidate = $request->query->get('novalidate', false);

        $form->handleRequest($request);
        if ($noValidate === false && $form->isValid()) {
            if (!$view->getId()) {
                //create a view for the business entity instance if we are currently on a virtual one
                $this->entityManager->persist($view);
            }

            //get the widget from the form
            $widget = $form->getData();

            //update fields of the widget
            $widget->setBusinessEntityId($entity);

            //persist the widget
            $this->entityManager->persist($widget);
            $this->entityManager->flush();

            $this->widgetMapManager->insert($widget, $view, $slotId, $position, $widgetReference);

            $this->entityManager->persist($view);
            $this->entityManager->flush();

            $widget->setCurrentView($view);

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
                'html'    => $this->widgetFormBuilder->renderNewForm($form, $widget, $slotId, $view, $entity),
            ];
        }

        return $response;
    }

    /**
     * edit a widget.
     *
     * @param Request $request
     * @param Widget  $widget
     * @param View    $currentView
     * @param string  $businessEntityId The entity name is used to know which form to submit
     *
     * @return template
     */
    public function editWidget(Request $request, Widget $widget, View $currentView, $businessEntityId = null, $widgetMode = Widget::MODE_STATIC)
    {
        /** @var BusinessEntity[] $classes */
        $classes = $this->cacheReader->getBusinessClassesForWidget($widget);

        //the id of the edited widget
        //a new widget might be created in the case of a legacy
        $initialWidgetId = $widget->getId();

        //the type of method used
        $requestMethod = $request->getMethod();

        //if the form is posted
        if ($requestMethod === 'POST') {
            //the widget view
            $widgetView = $currentView->getWidgetMapByWidget($widget)->getView();

            //we only copy the widget if the view of the widget is not the current view
            if ($widgetView !== $currentView) {
                $widget = $this->overwriteWidget($currentView, $widget);
            }
            if ($businessEntityId !== null) {
                $form = $this->widgetFormBuilder->buildForm($widget, $currentView, $businessEntityId, $classes[$businessEntityId]->getClass(), $widgetMode);
            } else {
                $form = $this->widgetFormBuilder->buildForm($widget, $currentView);
            }

            $noValidate = $request->query->get('novalidate', false);
            $form->handleRequest($request);
            if ($noValidate === false && $form->isValid()) {
                $widget->setBusinessEntityId($businessEntityId);

                $this->entityManager->persist($widget);

                $this->entityManager->persist($currentView);
                $this->entityManager->flush();

                $response = [
                    'view'        => $currentView,
                    'success'     => true,
                    'html'        => $this->widgetRenderer->render($widget, $currentView),
                    'widgetId'    => $initialWidgetId,
                    'viewCssHash' => $currentView->getCssHash(),
                ];
            } else {
                $formErrorHelper = $this->formErrorHelper;
                //Return a message for developer in console and form view in order to refresh view and show form errors
                $response = [
                    'success' => false,
                    'message' => $noValidate === false ? $formErrorHelper->getRecursiveReadableErrors($form) : null,
                    'html'    => $this->widgetFormBuilder->renderForm($form, $widget, $businessEntityId),
                ];
            }
        } else {
            $forms = $this->widgetFormBuilder->renderNewWidgetForms($widget->getSlot(), $currentView, $widget, $classes);

            $response = [
                'success'  => true,
                'html'     => $this->victoireTemplating->render(
                    'VictoireCoreBundle:Widget:Form/edit.html.twig',
                    [
                        'view'    => $currentView,
                        'classes' => $classes,
                        'forms'   => $forms,
                        'widget'  => $widget,
                    ]
                ),
            ];
        }

        return $response;
    }

    /**
     * Remove a widget.
     *
     * @param Widget $widget
     *
     * @return array The parameter for the view
     */
    public function deleteWidget(Widget $widget, View $view)
    {
        //update the view deleting the widget
        $this->widgetMapManager->delete($view, $widget);

        //we update the widget map of the view
        $this->widgetMapBuilder->build($view);
        //Used to update view in callback (we do it before delete it else it'll not exists anymore)
        $widgetId = $widget->getId();
        $widgetMap = $view->getWidgetMapByWidget($widget);
        //the widget is removed only if the current view is the view of the widget
        if (null !== $widgetMap
        && $widgetMap->getView() == $view
        && $widgetMap->getAction() != WidgetMap::ACTION_DELETE) {
            //we remove the widget
            $this->entityManager->remove($widget);
        }

        //we update the view
        $this->entityManager->persist($view);
        $this->entityManager->flush();

        return [
            'success'     => true,
            'widgetId'    => $widgetId,
            'viewCssHash' => $view->getCssHash(),
        ];
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

        //we have to persist the widget to get its id
        $this->entityManager->persist($view);
        $this->entityManager->flush();

        $originalWidgetMap = $view->getWidgetMapByWidget($widget);

        $this->widgetMapManager->overwrite($view, $originalWidgetMap, $widgetCopy);

        $this->widgetMapBuilder->build($view);

        return $widgetCopy;
    }

    /**
     * @param Widget $entity
     */
    public function cloneEntity($entity)
    {
        $entityCopy = clone $entity;

        //Look for on_to_many relations, if found, duplicate related entities.
        //It is necessary for 'list' widgets, this algo duplicates and persists list items.
        $associations = $this->entityManager->getClassMetadata(get_class($entityCopy))->getAssociationMappings();
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($associations as $name => $values) {
            if ($values['type'] === ClassMetadataInfo::ONE_TO_MANY && $values['fieldName'] != 'widgetMaps') {
                $relatedEntities = $accessor->getValue($entityCopy, $values['fieldName']);
                $relatedEntitiesCopies = [];
                foreach ($relatedEntities as $relatedEntity) {
                    $relatedEntityCopy = clone $relatedEntity;
                    $this->entityManager->persist($relatedEntity);
                    $relatedEntitiesCopies[] = $relatedEntityCopy;
                }
                $accessor->setValue($widgetCopy, $name, $relatedEntitiesCopies);
            }

            //Clone OneToOne relation objects
            if ($values['type'] === ClassMetadataInfo::ONE_TO_ONE) {
                $relatedEntity = $accessor->getValue($entityCopy, $values['fieldName']);
                if ($relatedEntity) {
                    $relatedEntityCopy = clone $relatedEntity;
                    $this->entityManager->persist($relatedEntity);
                    $accessor->setValue($widgetCopy, $name, $relatedEntityCopy);
                }
            }
        }

        $this->entityManager->persist($entityCopy);

        return $entityCopy;
    }
}
