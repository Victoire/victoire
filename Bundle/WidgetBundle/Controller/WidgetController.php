<?php

namespace Victoire\Bundle\WidgetBundle\Controller;

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Form\WidgetStyleType;
use Victoire\Bundle\WidgetMapBundle\Exception\WidgetMapNotFoundException;
use Victoire\Bundle\WidgetMapBundle\Helper\WidgetMapHelper;

/**
 * Widget Controller.
 */
class WidgetController extends Controller
{
    use VictoireAlertifyControllerTrait;

    /**
     * Show a widget.
     *
     * @param Request $request
     * @param Widget  $widget
     * @param int     $viewReferenceId
     *
     * @Route("/victoire-dcms-public/widget/show/{id}/{viewReferenceId}", name="victoire_core_widget_show", options={"expose"=true})
     * @Template()
     * @ParamConverter("id", class="VictoireWidgetBundle:Widget")
     *
     * @throws Exception
     *
     * @return Response
     */
    public function showAction(Request $request, Widget $widget, $viewReferenceId)
    {
        try {
            $view = $this->get('victoire_page.page_helper')->findPageByParameters(['id' => $viewReferenceId]);
            $this->get('victoire_widget_map.builder')->build($view);
            $this->get('victoire_core.current_view')->setCurrentView($view);
            $response = new JsonResponse([
                    'html'    => $this->get('victoire_widget.widget_renderer')->render($widget, $view),
                    'update'  => 'vic-widget-'.$widget->getId().'-container',
                    'success' => true,
                ]
            );
        } catch (Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * API widgets function.
     *
     * @param string $widgetIds       the widget ids to fetch in json
     * @param int    $viewReferenceId
     *
     * @Route("/victoire-dcms-public/api/widgets/{widgetIds}/{viewReferenceId}", name="victoire_core_widget_apiWidgets", options={"expose"=true})
     *
     * @return JsonResponse
     */
    public function apiWidgetsAction($widgetIds, $viewReferenceId)
    {
        $view = $this->get('victoire_page.page_helper')->findPageByParameters(['id' => $viewReferenceId]);
        $response = [];
        $widgets = $this->get('doctrine.orm.entity_manager')->getRepository('VictoireWidgetBundle:Widget')
            ->findBy(['id' => json_decode($widgetIds)]);

        foreach ($widgets as $widget) {
            $response[$widget->getId()] = $this->get('victoire_widget.widget_renderer')->render($widget, $view);
        }

        return new JsonResponse($response);
    }

    /**
     * New Widget.
     *
     * @param Request $request
     * @param string  $type          The type of the widget we edit
     * @param int     $viewReference The view reference where attach the widget
     * @param string  $slot          The slot where attach the widget
     * @param null    $quantum       The quantum number used to avoid same form name
     *
     * @throws Exception
     *
     * @return JsonResponse
     * @Route("/victoire-dcms/widget/new/{type}/{viewReference}/{slot}/{quantum}", name="victoire_core_widget_new", defaults={"slot":null, "quantum":0}, options={"expose"=true})
     */
    public function newAction(Request $request, $type, $viewReference, $slot = null, $quantum = 0)
    {
        try {
            $view = $this->getViewByReferenceId($viewReference);

            if (!$reference = $this->get('victoire_view_reference.repository')
                ->getOneReferenceByParameters(['id' => $viewReference])) {
                $reference = new ViewReference($viewReference);
            }
            $view->setReference($reference);

            $position = $request->query->has('position') ? $request->query->get('position') : null;
            $parentWidgetMap = $request->query->has('parentWidgetMap') ? $request->query->get('parentWidgetMap') : null;
            $widgetData = $this->get('victoire_widget.widget_manager')->newWidget(
                Widget::MODE_STATIC,
                $type,
                $slot,
                $view,
                $position,
                $parentWidgetMap,
                $quantum
            );

            $response = new JsonResponse([
                'success' => true,
                'html'    => $widgetData['html'],
            ]);
        } catch (Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * Create a widget.
     * This action needs 2 routes to handle the presence or not of "businessEntityId" and 'parentWidgetMap'
     * that are both integers but "businessEntityId" present only in !static mode.
     *
     * @param string $type             The type of the widget we edit
     * @param int    $viewReference    The view reference where attach the widget
     * @param string $slot             The slot where attach the widget
     * @param string $businessEntityId The BusinessEntity::id (can be null if the submitted form is in static mode)
     *
     * @return JsonResponse
     * @Route("/victoire-dcms/widget/create/static/{type}/{viewReference}/{slot}/{quantum}/{position}/{parentWidgetMap}", name="victoire_core_widget_create_static", defaults={"mode":"static", "slot":null, "businessEntityId":null, "position":null, "parentWidgetMap":null, "_format": "json", "quantum":0})
     * @Route("/victoire-dcms/widget/create/{mode}/{type}/{viewReference}/{slot}/{quantum}/{businessEntityId}/{position}/{parentWidgetMap}", name="victoire_core_widget_create", defaults={"slot":null, "businessEntityId":null, "position":null, "parentWidgetMap":null, "_format": "json", "quantum":0})
     * @Template()
     */
    public function createAction($mode, $type, $viewReference, $slot = null, $position = null, $parentWidgetMap = null, $businessEntityId = null, $quantum = null)
    {
        try {
            //services
            $view = $this->getViewByReferenceId($viewReference);

            $isNewPage = $view->getId() === null ? true : false;

            if (!$reference = $this->get('victoire_view_reference.repository')
                ->getOneReferenceByParameters(['id' => $viewReference])) {
                $reference = new ViewReference($viewReference);
            }

            $view->setReference($reference);
            $this->get('victoire_core.current_view')->setCurrentView($view);

            $this->congrat($this->get('translator')->trans('victoire.success.message', [], 'victoire'));
            $response = $this->get('widget_manager')->createWidget($mode, $type, $slot, $view, $businessEntityId, $position, $parentWidgetMap, $quantum);

            if ($isNewPage) {
                $response = new JsonResponse([
                    'success'  => true,
                    'redirect' => $this->generateUrl(
                        'victoire_core_page_show',
                        [
                            'url' => $reference->getUrl(),
                        ]
                    ),
                ]);
            } else {
                $response = new JsonResponse($response);
            }
        } catch (Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * Edit a widget.
     *
     * @param Widget $widget           The widget to edit
     * @param int    $viewReference    The current view
     * @param string $businessEntityId The BusinessEntity::id (can be null if the submitted form is in static mode)
     *
     * @return JsonResponse
     *
     * @Route("/victoire-dcms/widget/edit/{id}/{viewReference}/{mode}/{businessEntityId}", name="victoire_core_widget_edit", options={"expose"=true}, defaults={"quantum":0, "mode": "static"})
     * @Route("/victoire-dcms/widget/update/{id}/{viewReference}/{mode}/{quantum}/{businessEntityId}", name="victoire_core_widget_update", defaults={"businessEntityId": null, "mode": "static"})
     * @Template()
     */
    public function editAction(Widget $widget, $viewReference, $mode = Widget::MODE_STATIC, $quantum = null, $businessEntityId = null)
    {
        $view = $this->getViewByReferenceId($viewReference);
        $this->get('victoire_widget_map.builder')->build($view);
        $this->get('victoire_widget_map.widget_data_warmer')->warm($this->getDoctrine()->getManager(), $view);

        if ($view instanceof BusinessTemplate && !$reference = $this->get('victoire_view_reference.repository')
            ->getOneReferenceByParameters(['viewId' => $view->getId()])) {
            $reference = new ViewReference($viewReference);
            $view->setReference($reference);
        }
        $this->get('victoire_core.current_view')->setCurrentView($view);
        try {
            $response = new JsonResponse(
                $this->get('widget_manager')->editWidget(
                    $this->get('request'),
                    $widget,
                    $view,
                    $quantum,
                    $businessEntityId,
                    $mode
                )
            );

            $this->congrat($this->get('translator')->trans('victoire.success.message', [], 'victoire'));
        } catch (Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * @TODO Simplify Action when reorganize with editAction
     *
     * Stylize a widget.
     *
     * @param Widget $widget        The widget to stylize
     * @param int    $viewReference The current view
     *
     * @return JsonResponse
     *
     * @Route("/victoire-dcms/widget/stylize/{id}/{viewReference}", name="victoire_core_widget_stylize", options={"expose"=true})
     * @Template()
     */
    public function stylizeAction(Request $request, Widget $widget, $viewReference)
    {
        $view = $this->getViewByReferenceId($viewReference);
        $this->get('victoire_widget_map.builder')->build($view);

        try {
            $widgetView = WidgetMapHelper::getWidgetMapByWidgetAndView($widget, $view)->getView();
        } catch (WidgetMapNotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        if (!$view instanceof \Victoire\Bundle\TemplateBundle\Entity\Template) {
            $widgetViewReference = $this->get('victoire_view_reference.repository')
                ->getOneReferenceByParameters(['viewId' => $view->getId()]);
            $widgetView->setReference($widgetViewReference);
        }

        $this->get('victoire_core.current_view')->setCurrentView($view);
        try {
            if ($request->getMethod() === 'POST') {
                $form = $this->get('form.factory')->create(WidgetStyleType::class, $widget, [
                        'method' => 'POST',
                        'action' => $this->generateUrl(
                            'victoire_core_widget_stylize',
                            [
                                'id'            => $widget->getId(),
                                'viewReference' => $viewReference,
                            ]
                        ),
                    ]
                );

                $form->handleRequest($this->get('request'));
                if ($request->query->get('novalidate', false) === false && $form->isValid()) {
                    if ($form->has('deleteBackground') && $form->get('deleteBackground')->getData()) {
                        // @todo: dynamic responsive key
                        foreach (['', 'XS', 'SM', 'MD', 'LG'] as $key) {
                            $widget->{'deleteBackground'.$key}();
                        }
                    }
                    $this->get('doctrine.orm.entity_manager')->flush();
                    $params = [
                        'view'        => $view,
                        'success'     => true,
                        'html'        => $this->get('victoire_widget.widget_renderer')->render($widget, $view),
                        'widgetId'    => $widget->getId(),
                        'viewCssHash' => $view->getCssHash(),
                    ];
                } else {
                    $template = ($request->query->get('novalidate', false) !== false) ? 'VictoireCoreBundle:Widget/Form/stylize:form.html.twig' : 'VictoireCoreBundle:Widget/Form:stylize.html.twig';
                    $params = [
                        'success' => !$form->isSubmitted(),
                        'html'    => $this->get('templating')->render(
                            $template,
                            [
                                'view'   => $view,
                                'form'   => $form->createView(),
                                'widget' => $widget,
                            ]
                        ),
                    ];
                }
            } else {
                $widgets = $widget->getWidgetMap()->getWidgets();
                $forms = [];
                foreach ($widgets as $widget) {
                    $forms[] = $this->get('form.factory')->create(WidgetStyleType::class, $widget, [
                            'method' => 'POST',
                            'action' => $this->generateUrl(
                                'victoire_core_widget_stylize',
                                [
                                    'id'            => $widget->getId(),
                                    'viewReference' => $viewReference,
                                ]
                            ),
                        ]
                    )->createView();
                }
                $params = [
                    'html' => $this->get('templating')->render(
                        'VictoireCoreBundle:Widget/Form:stylize.html.twig',
                        [
                            'view'    => $view,
                            'forms'   => $forms,
                            'widget'  => $widget,
                            'widgets' => $widgets,
                        ]
                    ),
                ];
            }

            $response = new JsonResponse($params);
        } catch (Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * Delete a Widget.
     *
     * @param Widget $widget        The widget to delete
     * @param int    $viewReference The current view
     *
     * @return JsonResponse response
     * @Route("/victoire-dcms/widget/delete/{id}/{viewReference}", name="victoire_core_widget_delete", defaults={"_format": "json"})
     * @Template()
     */
    public function deleteAction(Widget $widget, $viewReference)
    {
        $view = $this->getViewByReferenceId($viewReference);
        try {
            $widgetId = $widget->getId();
            $this->get('widget_manager')->deleteWidget($widget, $view);

            return new JsonResponse([
                    'success'  => true,
                    'message'  => $this->get('translator')->trans('victoire_widget.delete.success', [], 'victoire'),
                    'widgetId' => $widgetId,
                ]
            );
        } catch (Exception $ex) {
            return $this->getJsonReponseFromException($ex);
        }
    }

    /**
     * Delete a Widget quantum.
     *
     * @param Widget $widget        The widget to delete
     * @param int    $viewReference The current view
     *
     * @return JsonResponse response
     * @Route("/victoire-dcms/widget/delete/quantum/{id}/{viewReference}", name="victoire_core_widget_delete_bulk", defaults={"_format": "json"})
     * @Template()
     */
    public function deleteBulkAction(Widget $widget, $viewReference)
    {
        $view = $this->getViewByReferenceId($viewReference);
        try {
            $widgets = $widget->getWidgetMap()->getWidgets();

            foreach ($widgets as $widget) {
                $this->get('widget_manager')->deleteWidget($widget, $view);
            }

            return new JsonResponse([
                    'success' => true,
                    'message' => $this->get('translator')->trans('victoire_widget.delete.success', [], 'victoire'),
                ]
            );
        } catch (Exception $ex) {
            return $this->getJsonReponseFromException($ex);
        }
    }

    /**
     * Unlink a Widget by id
     * -> used to unlink an invalid widget after a bad widget unplug.
     *
     * @param int $id            The widgetId to unlink
     * @param int $viewReference The current viewReference
     *
     * @return JsonResponse response
     * @Route("/victoire-dcms/widget/unlink/{id}/{viewReference}", name="victoire_core_widget_unlink", defaults={"_format": "json"}, options={"expose"=true})
     * @Template()
     */
    public function unlinkAction($id, $viewReference)
    {
        $view = $this->getViewByReferenceId($viewReference);
        try {
            $this->get('victoire_widget.widget_helper')->deleteById($id);
            $this->get('doctrine.orm.entity_manager')->flush();

            if ($view instanceof Template) {
                $redirect = $this->generateUrl('victoire_template_show', ['id' => $view->getId()]);
            } elseif ($view instanceof BusinessTemplate) {
                $redirect = $this->generateUrl('victoire_business_template_show', ['id' => $view->getId()]);
            } else {
                $viewReference = $this->get('victoire_view_reference.repository')
                    ->getOneReferenceByParameters(['viewId' => $view->getId()]);

                $redirect = $this->generateUrl('victoire_core_page_show', [
                        'url' => $viewReference->getUrl(),
                    ]);
            }

            return new JsonResponse([
                    'success'  => true,
                    'redirect' => $redirect,
                ]);
        } catch (Exception $ex) {
            return $this->getJsonReponseFromException($ex);
        }
    }

    /**
     * Update widget positions accross the view. If moved widget is a Reference, ask to detach the view from template.
     *
     * @param int $viewReference The current viewReference
     *
     * @return JsonResponse
     * @Route("/victoire-dcms/widget/updatePosition/{viewReference}", name="victoire_core_widget_update_position", options={"expose"=true})
     */
    public function updatePositionAction(Request $request, $viewReference)
    {
        $view = $this->getViewByReferenceId($viewReference);
        try {
            //the sorted order for the widgets
            $sortedWidget = $request->get('sorted');
            $em = $this->get('doctrine.orm.entity_manager');
            if (!$view->getId()) {
                //This view does not have an id, so it's a non persisted BEP. To keep this new order, well have to persist it.
                $em->persist($view);
                $em->flush();
            }
            $this->get('victoire_widget_map.builder')->build($view);
            //recompute the order for the widgets
            $this->get('victoire_widget_map.manager')->move($view, $sortedWidget);
            $em->flush();

            $this->get('victoire_widget_map.builder')->build($view);
            $availablePositions = $this->get('victoire_widget_map.builder')->getAvailablePosition($view);

            $response = new JsonResponse(['success' => true, 'availablePositions' => $availablePositions]);
        } catch (Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * Update widget positions accross the view. If moved widget is a Reference, ask to detach the view from template.
     *
     * @param int $viewReference The current viewReference
     *
     * @return JsonResponse
     * @Route("/victoire-dcms/widget/get-available-positions/{viewReference}", name="victoire_core_widget_get_available_positions", options={"expose"=true})
     */
    public function getAvailablePositionsAction(Request $request, $viewReference)
    {
        $view = $this->getViewByReferenceId($viewReference);

        $this->get('victoire_widget_map.builder')->build($view);
        $availablePositions = $this->get('victoire_widget_map.builder')->getAvailablePosition($view);

        return new JsonResponse($availablePositions);
    }

    /**
     * Get the json response by the exception and the current user.
     *
     * @param Exception $ex
     *
     * @return JsonResponse
     */
    protected function getJsonReponseFromException(Exception $ex)
    {
        //services
        $securityContext = $this->get('security.context');
        $logger = $this->get('logger');

        //can we see the debug
        $isDebugAllowed = $securityContext->isGranted('ROLE_VICTOIRE_PAGE_DEBUG') ? true : $this->get('kernel')->isDebug();

        //whatever is the exception, we log it
        $logger->error($ex->getMessage());
        $logger->error($ex->getTraceAsString());

        if ($isDebugAllowed) {
            throw $ex;
        } else {
            //translate the message
            $translator = $this->get('translator');

            //get the translated message
            $message = $translator->trans('error_occured', [], 'victoire');

            $response = new JsonResponse(
                [
                    'success' => false,
                    'message' => $message,
                ]
            );
        }

        return $response;
    }

    /**
     * @param int $referenceId
     */
    protected function getViewByReferenceId($referenceId)
    {
        return $this->get('victoire_page.page_helper')->findPageByParameters(['id' => $referenceId]);
    }
}
