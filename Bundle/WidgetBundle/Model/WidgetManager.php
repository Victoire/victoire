<?php

namespace Victoire\Bundle\WidgetBundle\Model;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Reader\BusinessEntityCacheReader;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Template\TemplateMapper;
use Victoire\Bundle\FormBundle\Helper\FormErrorHelper;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
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
    protected $entityManager;
    protected $formErrorHelper; // @victoire_form.error_helper
    protected $request; // @request
    protected $widgetMapManager;
    protected $cacheReader; // @victoire_business_entity.cache_reader
    protected $victoireTemplating;
    protected $pageHelper;
    protected $slots; // %victoire_core.slots%

    /**
     * construct
     * @param WidgetHelper              $widgetHelper
     * @param WidgetFormBuilder         $widgetFormBuilder
     * @param WidgetContentResolver     $widgetContentResolver
     * @param WidgetRenderer            $widgetRenderer
     * @param EntityManager             $entityManager
     * @param FormErrorHelper           $formErrorHelper
     * @param Request                   $request
     * @param WidgetMapManager          $widgetMapManager
     * @param WidgetMapHelper           $widgetMapHelper
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
        WidgetMapHelper $widgetMapHelper,
        WidgetMapBuilder $widgetMapBuilder,
        BusinessEntityCacheReader $cacheReader,
        TemplateMapper $victoireTemplating,
        PageHelper $pageHelper,
        $slots
    )
    {
        $this->widgetFormBuilder = $widgetFormBuilder;
        $this->widgetHelper = $widgetHelper;
        $this->widgetContentResolver = $widgetContentResolver;
        $this->widgetRenderer = $widgetRenderer;
        $this->entityManager = $entityManager;
        $this->formErrorHelper = $formErrorHelper;
        $this->request = $request;
        $this->widgetMapManager = $widgetMapManager;
        $this->widgetMapHelper = $widgetMapHelper;
        $this->widgetMapBuilder = $widgetMapBuilder;
        $this->cacheReader = $cacheReader;
        $this->victoireTemplating = $victoireTemplating;
        $this->pageHelper = $pageHelper;
        $this->slots = $slots;
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
    public function newWidget($mode, $type, $slot, $view, $position)
    {
        $widget = $this->widgetHelper->newWidgetInstance($type, $view, $slot, $mode);

        /** @var BusinessEntity[] $classes */
        $classes = $this->cacheReader->getBusinessClassesForWidget($widget);
        $forms = $this->widgetFormBuilder->renderNewWidgetForms($slot, $view, $widget, $classes, $position);

        return array(
            "html" => $this->victoireTemplating->render(
                "VictoireCoreBundle:Widget:Form/new.html.twig",
                array(
                    'view'    => $view,
                    'classes' => $classes,
                    'widget'  => $widget,
                    'forms'   => $forms,
                )
            ),
        );
    }

    /**
     * Create a widget
     * @param string  $mode
     * @param string  $type
     * @param string  $slotId
     * @param View    $view
     * @param string  $entity
     * @param integer $positionReference
     *
     * @param string $type
     * @throws \Exception
     * @return Template
     *
     */
    public function createWidget($mode, $type, $slotId, View $view, $entity, $positionReference)
    {
        //services
        $formErrorHelper = $this->formErrorHelper;
        $request = $this->request;

        //create a new widget
        $widget = $this->widgetHelper->newWidgetInstance($type, $view, $slotId, $mode);

        $form = $this->widgetFormBuilder->callBuildFormSwitchParameters($widget, $view, $entity, $positionReference);

        $form->handleRequest($request);
        if ($request->query->get('novalidate', false) === false && $form->isValid()) {
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

            //create the new widget map
            $widgetMapEntry = new WidgetMap();
            $widgetMapEntry->setAction(WidgetMap::ACTION_CREATE);
            $widgetMapEntry->setWidgetId($widget->getId());

            $widgetMap = $this->widgetMapBuilder->build($view, false);

            $widgetMapEntry = $this->widgetMapHelper->generateWidgetPosition($widgetMapEntry, $widget, $widgetMap, $positionReference);
            $this->widgetMapHelper->insertWidgetMapInSlot($slotId, $widgetMapEntry, $view);

            $this->entityManager->persist($view);
            $this->entityManager->flush();

            $widget->setCurrentView($view);

            //get the html for the widget
            $htmlWidget = $this->widgetRenderer->renderContainer($widget, $view, $widgetMapEntry->getPosition());

            $response = array(
                "success"  => true,
                'widgetId' => $widget->getId(),
                "html"     => $htmlWidget,
            );
        } else {
            //get the errors as a string
            $response = array(
                "success" => false,
                "message" => $formErrorHelper->getRecursiveReadableErrors($form),
                "html"    => $this->widgetFormBuilder->renderNewForm($form, $widget, $slotId, $view, $entity),
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
     * @param string  $businessEntityId  The entity name is used to know which form to submit
     *
     * @return template
     */
    public function editWidget(Request $request, Widget $widget, View $currentView, $businessEntityId = null, $widgetMode = Widget::MODE_STATIC)
    {
        /** @var BusinessEntity[] $classes */
        $classes = $this->cacheReader->getBusinessClassesForWidget($widget);

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
            if ($businessEntityId !== null) {
                $form = $this->widgetFormBuilder->buildForm($widget, $currentView, $businessEntityId, $classes[$businessEntityId]->getClass(), $widgetMode);
            } else {
                $form = $this->widgetFormBuilder->buildForm($widget, $currentView);
            }

            $form->handleRequest($request);

            if ($request->query->get('novalidate', false) === false && $form->isValid()) {
                $widget->setBusinessEntityId($businessEntityId);

                $this->entityManager->persist($widget);

                //update the widget map by the slots
                $currentView->updateWidgetMapBySlots();
                $this->entityManager->persist($currentView);
                $this->entityManager->flush();

                $response = array(
                    'view'        => $currentView,
                    'success'     => true,
                    'html'        => $this->widgetRenderer->render($widget, $currentView),
                    'widgetId'    => $initialWidgetId,
                );
            } else {
                $formErrorHelper = $this->formErrorHelper;
                //Return a message for developer in console and form view in order to refresh view and show form errors
                $response = array(
                    "success" => false,
                    "message" => $formErrorHelper->getRecursiveReadableErrors($form),
                    "html"    => $this->widgetFormBuilder->renderForm($form, $widget, $businessEntityId),
                );
            }
        } else {
            $forms = $this->widgetFormBuilder->renderNewWidgetForms($widget->getSlot(), $currentView, $widget, $classes);

            $response = array(
                "success"  => true,
                "html"     => $this->victoireTemplating->render(
                    "VictoireCoreBundle:Widget:Form/edit.html.twig",
                    array(
                        'view'    => $currentView,
                        'classes' => $classes,
                        'forms'   => $forms,
                        'widget'  => $widget,
                    )
                ),
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
            $this->entityManager->remove($widget);
        }

        //we update the view
        $this->entityManager->persist($view);
        $this->entityManager->flush();

        return array(
            "success"  => true,
            "widgetId" => $widgetId,
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
        $associations = $this->entityManager->getClassMetadata(get_class($widget))->getAssociationMappings();
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($associations as $name => $values) {
            if ($values['type'] === ClassMetadataInfo::ONE_TO_MANY) {
                $relatedEntities = $accessor->getValue($widget, $values['fieldName']);
                $relatedEntitiesCopies = array();
                foreach ($relatedEntities as $relatedEntity) {
                    $relatedEntityCopy = clone $relatedEntity;
                    $this->entityManager->persist($relatedEntity);
                    $relatedEntitiesCopies[] = $relatedEntityCopy;
                }
                $accessor->setValue($widgetCopy, $name, $relatedEntitiesCopies);
            }

            //Clone OneToOne relation objects
            if ($values['type'] === ClassMetadataInfo::ONE_TO_ONE) {
                $relatedEntity = $accessor->getValue($widget, $values['fieldName']);
                if ($relatedEntity) {
                    $relatedEntityCopy = clone $relatedEntity;
                    $this->entityManager->persist($relatedEntity);
                    $accessor->setValue($widgetCopy, $name, $relatedEntityCopy);
                }
            }
        }

        //we have to persist the widget to get its id
        $this->entityManager->persist($view);
        $this->entityManager->persist($widgetCopy);
        $this->entityManager->flush();

        $this->widgetMapManager->overwriteWidgetMap($widgetCopy, $widget, $view);

        return $widgetCopy;
    }
}
