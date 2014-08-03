<?php

namespace Victoire\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppVentus\Awesome\ShortcutsBundle\Controller\AwesomeController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\CoreBundle\Entity\Widget;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\CoreBundle\Widget\Managers\WidgetManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Widget Controller
 *
 * @Route("/victoire-dcms/widget")
 */
class WidgetController extends AwesomeController
{
    /**
     * Show a widget
     *
     * @param  Widget   $widget the widget to show
     * @return response
     * @Route("/show/{id}/{entity}", name="victoire_core_widget_show", options={"expose"=true}, defaults={"entity": null})
     * @Template()
     * @ParamConverter("id", class="VictoireCoreBundle:Widget")
     */
    public function showAction(Widget $widget, $entity = null)
    {
        //the response is for the ajax.js from the AppVentus Ajax Bundle
        try {

            if ($this->getRequest()->isXmlHttpRequest()) {
                $widgetManager = $this->getWidgetManager();

                 $response = new JsonResponse(array(
                     'html' => $widgetManager->render($widget, false, $entity),
                     'update' => 'vic-widget-'.$widget->getId().'-container',
                     'success' => false
                 ));
            } else {
                $response = $this->redirect($this->generateUrl('victoire_core_page_show', array('url' => $widget->getPage()->getUrl())));
            }
        } catch (\Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * Edit a widget
     *
     * @param  Widget   $widget The widget to edit
     * @param  string   $type   The type of widget we edit
     * @return response
     *
     * @Route("/edit/{id}/{type}/{entity}", name="victoire_core_widget_edit", defaults={"type": null})
     * @Route("/update/{id}/{type}/{entity}", name="victoire_core_widget_update", defaults={"type": null, "entity": null})
     * @Template()
     * @ParamConverter("id", class="VictoireCoreBundle:Widget")
     */
    public function editAction(Request $request, Widget $widget, $type = null, $entity = null)
    {
        try {
            $widgetManager = $this->getWidgetManager();
            $response = new JsonResponse($widgetManager->edit($request, $widget, $type, $entity));
        } catch (\Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * New Widget
     *
     * @param  string         $type   The type of the widget we edit
     * @param  Page           $page   The page where attach the widget
     * @param  string         $slot   The slot where attach the widget
     * @param  BusinessEntity $entity The business entity the widget shows on dynamic mode
     * @return response
     *
     * @Route("/new/{type}/{page}/{slot}/{entity}", name="victoire_core_widget_new", defaults={"slot":null, "entity":null}, options={"expose"=true})
     * @Template()
     */
    public function newAction($type, $page, $slot = null, $entity = null)
    {
        try {
            $page = $this->get('doctrine.orm.entity_manager')->getRepository('VictoirePageBundle:Page')->findOneById($page);

            if ($entity) {
                $widgetManager = $this->get('widget_manager')->getManager(null, $type);
                $widget = $widgetManager->newWidget($page, $slot);

                $namespace = null;

                if ($entity === 'static' || $entity === '') {
                    $entity = null;
                }

                if ($entity) {
                    $annotationReader = $this->get('victoire_core.annotation_reader');
                    $classes = $annotationReader->getBusinessClassesForWidget($widget);

                    if (!isset($classes[$entity])) {
                        throw new \Exception('The widget type ['.$entity.'] does not exists.');
                    }

                    $namespace = $classes[$entity];
                }

                $form = $this->get('widget_manager')->buildForm($widgetManager, $widget, $entity, $namespace);

                $response = new JsonResponse($this->get('widget_manager')->renderNewForm($form, $widget, $slot, $page, $entity));
            } else {
                $response = new JsonResponse($this->get('widget_manager')->newWidget($type, $slot, $page));
            }
        } catch (\Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * Create a widget
     *
     * @param  string         $type   The type of the widget we edit
     * @param  Page           $page   The page where attach the widget
     * @param  string         $slot   The slot where attach the widget
     * @param  BusinessEntity $entity The business entity the widget shows on dynamic mode
     * @return response
     * @Route("/create/{type}/{page}/{slot}/{entity}", name="victoire_core_widget_create", defaults={"slot":null, "entity":null, "_format": "json"})
     * @Template()
     */
    public function createAction($type, $page, $slot = null, $entity = null)
    {
        try {
            //services
            $em = $this->getEntityManager();

            $page = $em->getRepository('VictoirePageBundle:Page')->findOneById($page);
            $widgetManager = $this->getWidgetManager();

            $response = new JsonResponse($widgetManager->createWidget($type, $slot, $page, $entity));
        } catch (\Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * Delete a Widget
     *
     * @param  Widget $widget The widget to delete
     * @return empty  response
     * @Route("/delete/{id}", name="victoire_core_widget_delete", defaults={"_format": "json"})
     * @Template()
     * @ParamConverter("id", class="VictoireCoreBundle:Widget")
     */
    public function deleteAction(Widget $widget)
    {
        try {
            $response = new JsonResponse($this->get('widget_manager')->deleteWidget($widget));
        } catch (\Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * Update widget positions accross the page. If moved widget is a Reference, ask to detach the page from template
     *
     * @param  Page     $page The page where update widget positions
     * @return response
     * @Route("/position/{page}", name="victoire_core_widget_update_position", options={"expose"=true})
     * @ParamConverter("page", class="VictoirePageBundle:Page")
     */
    public function updatePositionAction(Page $page)
    {
        try {
            //the sorted order for the widgets
            $sortedWidgets = $this->getRequest()->request->get('sorted');

            //recompute the order for the widgets
            $this->get('widget_manager')->updateWidgetMapOrder($page, $sortedWidgets);

            $response = new JsonResponse(array('success' => true));
        } catch (\Exception $ex) {
            $response = $this->getJsonReponseFromException($ex);
        }

        return $response;
    }

    /**
     * Shortcut for getting the widget manager
     *
     * @return WidgetManager
     */
    protected function getWidgetManager()
    {
        $manager = $this->get('widget_manager');

        return $manager;
    }

    /**
     * Get the json response by the exception and the current user
     *
     * @param \Exception $ex
     *
     * @return JsonResponse
     */
    protected function getJsonReponseFromException(\Exception $ex)
    {
        //services
        $securityContext = $this->get('security.context');
        $logger = $this->get('logger');

        //can we see the debug
        $isDebugAllowed = $securityContext->isGranted('ROLE_PAGE_DEBUG');

        //whatever is the exception, we log it
        $logger->error($ex->getMessage());
        $logger->error($ex->getTraceAsString());

        if ($isDebugAllowed) {
            throw $ex;
        } else {
            //translate the message
            $translator = $this->get('translator');

            //get the translated message
            $message = $translator->trans('error_occured', array(), 'victoire');

            $response = new JsonResponse(
                array(
                    'success' => false,
                    'message' => $message
                )
            );
        }

        return $response;
    }
}
