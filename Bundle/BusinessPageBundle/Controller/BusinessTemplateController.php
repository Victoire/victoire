<?php

namespace Victoire\Bundle\BusinessPageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Form\BusinessTemplateType;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * BusinessTemplate controller.
 *
 * @Route("/victoire-dcms/business-template")
 */
class BusinessTemplateController extends Controller
{
    use VictoireAlertifyControllerTrait;

    /**
     * List all business entity page pattern.
     *
     * @Route("/", name="victoire_business_template_index")
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');

        //services
        $em = $this->get('doctrine.orm.entity_manager');

        //the repository
        $repository = $em->getRepository('VictoireBusinessPageBundle:BusinessTemplate');

        $BusinessTemplates = [];

        $businessEntities = $businessEntityHelper->getBusinessEntities();

        foreach ($businessEntities as $businessEntity) {
            $name = $businessEntity->getName();

            //retrieve the pagePatterns
            $pagePatterns = $repository->findPagePatternByBusinessEntity($businessEntity);

            $BusinessTemplates[$name] = $pagePatterns;
        }

        return new JsonResponse([
                'html'    => $this->container->get('templating')->render(
                    'VictoireBusinessPageBundle:BusinessEntity:index.html.twig',
                    [
                        'businessEntities'           => $businessEntities,
                        'BusinessTemplates'          => $BusinessTemplates,
                    ]
                ),
                'success' => true,
            ]);
    }

    /**
     * show BusinessTemplate.
     *
     * @Route("/show/{id}", name="victoire_business_template_show")
     * @ParamConverter("template", class="VictoireBusinessPageBundle:BusinessTemplate")
     *
     * @return Response
     */
    public function showAction(BusinessTemplate $view)
    {
        //add the view to twig
        $this->get('twig')->addGlobal('view', $view);
        $view->setReference(new ViewReference($view->getId()));

        $this->get('victoire_widget_map.builder')->build($view);
        $this->get('victoire_widget_map.widget_data_warmer')->warm(
            $this->get('doctrine.orm.entity_manager'),
            $view
        );

        $this->container->get('victoire_core.current_view')->setCurrentView($view);

        return $this->container->get('victoire_page.page_helper')->renderPage($view);
    }

    /**
     * Creates a new BusinessTemplate entity.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("{id}/create", name="victoire_business_template_create")
     * @Method("POST")
     * @Template("VictoireBusinessPageBundle:BusinessTemplate:new.html.twig")
     *
     * @return JsonResponse
     */
    public function createAction(Request $request, $id)
    {
        //get the business entity
        $businessEntity = $this->getBusinessEntity($id);

        /** @var BusinessTemplate $view */
        $view = $this->get('victoire_business_page.BusinessTemplate_chain')->getBusinessTemplate($id);
        $view->setBusinessEntityId($businessEntity->getId());

        $form = $this->createCreateForm($view);

        $form->handleRequest($request);

        $params = [
            'success' => false,
        ];

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($view);
            $em->flush();

            //redirect to the page of the pagePattern
            $params['url'] = $this->generateUrl('victoire_business_template_show', ['id' => $view->getId()]);
            $params['success'] = true;

            $this->congrat($this->get('translator')->trans('victoire.business_template.create.success', [], 'victoire'));
        } else {
            //get the errors as a string
            $params['message'] = $this->container->get('victoire_form.error_helper')->getRecursiveReadableErrors($form);
        }

        return new JsonResponse($params);
    }

    /**
     * Creates a form to create a BusinessTemplate entity.
     *
     * @param BusinessTemplate $view The entity
     *
     * @return \Symfony\Component\Form\Form The form
     * @return Form
     */
    private function createCreateForm(BusinessTemplate $view)
    {
        $id = $view->getBusinessEntityId();

        $businessProperties = $this->getBusinessProperties($view);
        $form = $this->createForm(
            BusinessTemplateType::class,
            $view,
            [
                'action'           => $this->generateUrl('victoire_business_template_create', ['id' => $id]),
                'method'           => 'POST',
                'vic_business_properties' => $businessProperties,
            ]
        );

        return $form;
    }

    /**
     * Displays a form to create a new BusinessTemplate entity.
     *
     * @param string $id The id of the businessEntity
     *
     * @Route("/{id}/new", name="victoire_business_template_new")
     * @Method("GET")
     * @Template()
     *
     * @return JsonResponse The entity and the form
     */
    public function newAction($id)
    {
        //get the business entity
        $businessEntity = $this->getBusinessEntity($id);

        /** @var BusinessTemplate $view */
        $view = $this->get('victoire_business_page.BusinessTemplate_chain')->getBusinessTemplate($id);
        $view->setBusinessEntityId($businessEntity->getId());

        $form = $this->createCreateForm($view);

        $parameters = [
            'entity'             => $view,
            'form'               => $form->createView(),
        ];

        return new JsonResponse([
            'html' => $this->container->get('templating')->render(
                'VictoireBusinessPageBundle:BusinessTemplate:new.html.twig',
                $parameters
            ),
            'success' => true,
        ]);
    }

    /**
     * Displays a form to edit an existing BusinessTemplate entity.
     *
     * @Route("/{id}/edit", name="victoire_business_template_edit")
     * @Method("GET")
     * @Template()
     * @ParamConverter("id", class="VictoireCoreBundle:View")
     *
     * @throws \Exception
     *
     * @return JsonResponse The entity and the form
     */
    public function editAction(View $view)
    {
        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createEditForm($view);
        $deleteForm = $this->createDeleteForm($view->getId());


        $parameters = [
            'entity'             => $view,
            'form'               => $editForm->createView(),
            'delete_form'        => $deleteForm->createView(),
        ];

        return new JsonResponse([
            'html' => $this->container->get('templating')->render(
                'VictoireBusinessPageBundle:BusinessTemplate:edit.html.twig',
                $parameters
            ),
            'success' => true,
        ]);
    }

    /**
     * Creates a form to edit a BusinessTemplate entity.
     *
     * @param BusinessTemplate $view The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(BusinessTemplate $view)
    {
        $businessProperties = $this->getBusinessProperties($view);

        $form = $this->createForm(BusinessTemplateType::class, $view, [
            'action'           => $this->generateUrl('victoire_business_template_update', ['id' => $view->getId()]),
            'method'           => 'PUT',
            'vic_business_properties' => $businessProperties,
        ]);

        return $form;
    }

    /**
     * Edits an existing BusinessTemplate entity.
     *
     * @param Request $request
     * @param string  $id
     *
     * @Route("/{id}", name="victoire_business_template_update")
     * @Method("PUT")
     * @Template("VictoireBusinessPageBundle:BusinessTemplate:edit.html.twig")
     *
     * @throws \Exception
     *
     * @return JsonResponse The parameter for the response
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var BusinessTemplate $pagePattern */
        $pagePattern = $em->getRepository('VictoireBusinessPageBundle:BusinessTemplate')->find($id);

        if (!$pagePattern) {
            throw $this->createNotFoundException('Unable to find BusinessTemplate entity.');
        }

        $editForm = $this->createEditForm($pagePattern);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            //redirect to the page of the template
            $completeUrl = $this->generateUrl('victoire_business_template_show', ['id' => $pagePattern->getId()]);
            $message = $this->get('translator')->trans('victoire.business_template.edit.success', [], 'victoire');

            $success = true;
        } else {
            $success = false;
            $completeUrl = null;
            $message = $this->get('translator')->trans('victoire.business_template.edit.error', [], 'victoire');
        }

        return new JsonResponse([
            'success' => $success,
            'url'     => $completeUrl,
            'message' => $message,
        ]);
    }

    /**
     * Deletes a BusinessTemplate entity.
     *
     * @param Request $request
     * @param string  $id
     *
     * @Route("/{id}", name="victoire_business_template_delete")
     * @Method("DELETE")
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $view = $em->getRepository('VictoireBusinessPageBundle:BusinessTemplate')->find($id);

            if (!$view) {
                throw $this->createNotFoundException('Unable to find BusinessTemplate entity.');
            }

            $em->remove($view);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('victoire_business_template_index'));
    }

    /**
     * Creates a form to delete a BusinessTemplate entity by id.
     *
     * @param string $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('victoire_business_template_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'Delete'])
            ->getForm();
    }

    /**
     * List the entities that matches the query of the BusinessTemplate.
     *
     * @param BusinessTemplate $view
     *
     * @Route("/listEntities/{id}", name="victoire_business_template_listentities")
     * @ParamConverter("id", class="VictoireBusinessPageBundle:BusinessTemplate")
     * @Template
     *
     * @throws Exception
     *
     * @return array|Response The list of items for this template
     */
    public function listEntitiesAction(BusinessTemplate $view)
    {
        //services
        $bepHelper = $this->get('victoire_business_page.business_page_helper');

        //parameters for the view
        return [
            'BusinessTemplate'          => $view,
            'items'                     => $bepHelper->getEntitiesAllowed($view, $this->get('doctrine.orm.entity_manager')),
        ];
    }

    /**
     * Get an array of business properties by the business entity page pattern.
     *
     * @param BusinessTemplate $view
     *
     * @return array of business properties
     */
    private function getBusinessProperties(BusinessTemplate $view)
    {
        $businessTemplateHelper = $this->get('victoire_business_page.business_page_helper');
        //the business property link to the page
        $businessEntityId = $view->getBusinessEntityId();
        $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($businessEntityId);

        $businessProperties = $businessTemplateHelper->getBusinessProperties($businessEntity);

        return $businessProperties;
    }

    /**
     * @param string $id The id of the business entity
     *
     * @throws Exception If the business entity was not found
     *
     * @return template
     */
    private function getBusinessEntity($id)
    {
        //services
        $businessEntityManager = $this->get('victoire_core.helper.business_entity_helper');

        //get the businessEntity
        $businessEntity = $businessEntityManager->findById($id);

        //test the result
        if ($businessEntity === null) {
            throw new \Exception('The business entity ['.$id.'] was not found.');
        }

        return $businessEntity;
    }
}
