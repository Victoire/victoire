<?php

namespace Victoire\Bundle\BusinessEntityPageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * BusinessEntityPagePattern controller.
 *
 * @Route("/victoire-dcms/business-entity-page-pattern/businessentitypagepattern")
 */
class BusinessEntityPagePatternController extends Controller
{
    use VictoireAlertifyControllerTrait;

    /**
     * Creates a new BusinessEntityPagePattern entity.
     *
     * @param Request $request
     * @param integer $id
     *
     * @Route("{id}/create", name="victoire_bepp_create")
     * @Method("POST")
     * @Template("VictoireBusinessEntityPageBundle:BusinessEntityPagePattern:new.html.twig")
     *
     * @return JsonResponse
     */
    public function createAction(Request $request, $id)
    {
        //get the business entity
        $businessEntity = $this->getBusinessEntity($id);

        $view = new BusinessEntityPagePattern();
        $view->setBusinessEntityName($businessEntity->getName());

        $form = $this->createCreateForm($view);

        $form->handleRequest($request);

        $params = array(
            'success' => false
        );

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($view);
            $em->flush();

            //redirect to the page of the pagePattern
            $params['url'] = $this->generateUrl('victoire_core_page_show', array('url' => $view->getUrl()));
            $params['success'] = true;

            $this->congrat($this->get('translator')->trans('victoire.business_entity_page_pattern.create.success', array(), 'victoire'));
        } else {
            //get the errors as a string
            $params['message'] = $this->container->get('victoire_form.error_helper')->getRecursiveReadableErrors($form);
        }

        return new JsonResponse($params);
    }

    /**
     * Creates a form to create a BusinessEntityPagePattern entity.
     *
     * @param BusinessEntityPagePattern $view The entity
     *
     * @return \Symfony\Component\Form\Form The form
     *
     * @return Form
     */
    private function createCreateForm(BusinessEntityPagePattern $view)
    {
        $businessEntityName = $view->getBusinessEntityName();
        $businessProperty = $this->getBusinessProperties($view);

        $form = $this->createForm(
            'victoire_business_entity_page_pattern_type',
            $view,
            array(
                'action'           => $this->generateUrl('victoire_bepp_create', array('id' => $businessEntityName)),
                'method'           => 'POST',
                'businessProperty' => $businessProperty,
            )
        );

        return $form;
    }

    /**
     * Displays a form to create a new BusinessEntityPagePattern entity.
     * @param string $id The id of the businessEntity
     *
     * @Route("/{id}/new", name="victoire_bepp_new")
     * @Method("GET")
     * @Template()
     *
     * @return JsonResponse The entity and the form
     */
    public function newAction($id)
    {
        //get the business entity
        $businessEntity = $this->getBusinessEntity($id);

        $view = new BusinessEntityPagePattern();
        $view->setBusinessEntityName($businessEntity->getName());

        $form = $this->createCreateForm($view);

        $businessEntityHelper = $this->get('victoire_business_entity_page.business_entity_page_helper');
        $businessProperties = $businessEntityHelper->getBusinessProperties($businessEntity);

        $parameters = array(
            'entity' => $view,
            'form'   => $form->createView(),
            'businessProperties' => $businessProperties,
        );

        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityPageBundle:BusinessEntityPagePattern:new.html.twig',
                $parameters
            ),
            'success' => true,
        ));
    }

    /**
     * Displays a form to edit an existing BusinessEntityPagePattern entity.
     *
     * @Route("/{id}/edit", name="victoire_bepp_edit")
     * @Method("GET")
     * @Template()
     * @ParamConverter("id", class="VictoireCoreBundle:View")
     *
     * @return JsonResponse The entity and the form
     *
     * @throws \Exception
     */
    public function editAction(View $view)
    {
        $em = $this->getDoctrine()->getManager();
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');
        $businessEntityPagePatternHelper = $this->get('victoire_business_entity_page.business_entity_page_helper');

        $editForm = $this->createEditForm($view);
        $deleteForm = $this->createDeleteForm($view->getId());

        //the business property link to the page
        $businessEntityId = $view->getBusinessEntityName();
        $businessEntity = $businessEntityHelper->findById($businessEntityId);


        $businessProperties = $businessEntityPagePatternHelper->getBusinessProperties($businessEntity);

        $parameters = array(
            'entity'      => $view,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'businessProperties' => $businessProperties,
        );

        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityPageBundle:BusinessEntityPagePattern:edit.html.twig',
                $parameters
            ),
            'success' => true
        ));
    }

    /**
     * Creates a form to edit a BusinessEntityPagePattern entity.
     *
     * @param BusinessEntityPagePattern $view The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(BusinessEntityPagePattern $view)
    {
        $businessProperty = $this->getBusinessProperties($view);

        $form = $this->createForm('victoire_business_entity_page_pattern_type', $view, array(
            'action' => $this->generateUrl('victoire_bepp_update', array('id' => $view->getId())),
            'method' => 'PUT',
            'businessProperty' => $businessProperty,
        ));

        return $form;
    }
    /**
     * Edits an existing BusinessEntityPagePattern entity.
     * @param Request $request
     * @param string  $id
     *
     * @Route("/{id}", name="victoire_bepp_update")
     * @Method("PUT")
     * @Template("VictoireBusinessEntityPageBundle:BusinessEntityPagePattern:edit.html.twig")
     *
     * @return JsonResponse The parameter for the response
     *
     * @throws \Exception
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $pagePattern = $em->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPagePattern')->find($id);

        if (!$pagePattern) {
            throw $this->createNotFoundException('Unable to find BusinessEntityPagePattern entity.');
        }

        $editForm = $this->createEditForm($pagePattern);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            //get the url of the template
            $pagePattern = $pagePattern->getUrl();

            //redirect to the page of the template
            $completeUrl = $this->generateUrl('victoire_core_page_show', array('url' => $pagePattern));
            $message = $this->get('translator')->trans('victoire.business_entity_page_pattern.edit.success', array(), 'victoire');

            $success = true;
        } else {
            $success = false;
            $completeUrl = null;
            $message = $this->get('translator')->trans('victoire.business_entity_page_pattern.edit.error', array(), 'victoire');
        }

        return new JsonResponse(array(
            'success' => $success,
            'url'     => $completeUrl,
            'message' => $message,
        ));
    }

    /**
     * Deletes a BusinessEntityPagePattern entity.
     * @param Request $request
     * @param string  $id
     *
     * @Route("/{id}", name="victoire_bepp_delete")
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
            $view = $em->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPagePattern')->find($id);

            if (!$view) {
                throw $this->createNotFoundException('Unable to find BusinessEntityPagePattern entity.');
            }

            $em->remove($view);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('victoire_businessentitypage_businessentity_index'));
    }

    /**
     * Creates a form to delete a BusinessEntityPagePattern entity by id.
     *
     * @param string $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('victoire_bepp_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }

    /**
     * List the entities that matches the query of the businessEntityPagePattern
     * @param BusinessEntityPagePattern $view
     *
     * @Route("listEntities/{id}", name="victoire_bepp_listentities")
     * @ParamConverter("id", class="VictoireBusinessEntityPageBundle:BusinessEntityPagePattern")
     * @Template
     * @return array|Response The list of items for this template
     *
     * @throws Exception
     */
    public function listEntitiesAction(BusinessEntityPagePattern $view)
    {
        //services
        $bepHelper = $this->get('victoire_business_entity_page.business_entity_page_helper');

        //parameters for the view
        return array(
            'businessEntityPagePattern' => $view,
            'items'                     => $bepHelper->getEntitiesAllowed($view),
        );
    }

    /**
     * Get an array of business properties by the business entity page pattern
     *
     * @param BusinessEntityPagePattern $view
     *
     * @return array of business properties
     */
    private function getBusinessProperties(BusinessEntityPagePattern $view)
    {
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');
        //the name of the business entity link to the business entity page pattern
        $businessEntityName = $view->getBusinessEntityName();

        $businessEntity = $businessEntityHelper->findById($businessEntityName);
        $businessProperties = $businessEntity->getBusinessPropertiesByType('businessParameter');

        $businessProperty = array();

        foreach ($businessProperties as $bp) {
            $entityProperty = $bp->getEntityProperty();
            $businessProperty[$entityProperty] = $entityProperty;
        }

        return $businessProperty;
    }

    /**
     *
     * @param string $id The id of the business entity
     *
     * @return template
     *
     * @throws Exception If the business entity was not found
     *
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
