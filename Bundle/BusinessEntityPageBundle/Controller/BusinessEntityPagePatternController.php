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

/**
 * BusinessEntityPagePattern controller.
 *
 * @Route("/victoire-dcms/business-entity-page-pattern/businessentitypagepattern")
 */
class BusinessEntityPagePatternController extends Controller
{
    /**
     * Creates a new BusinessEntityPagePattern entity.
     *
     * @param Request $request
     * @param integer $id
     *
     * @Route("{id}/create", name="victoire_businessentitypagepattern_businessentitypagepattern_create")
     * @Method("POST")
     * @Template("VictoireBusinessEntityPageBundle:BusinessEntityPagePattern:new.html.twig")
     *
     * @return Ambiguous \Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern NULL
     */
    public function createAction(Request $request, $id)
    {
        //get the business entity
        $businessEntity = $this->getBusinessEntity($id);
        $errorMessage = '';

        $entity = new BusinessEntityPagePattern();
        $entity->setBusinessEntityName($businessEntity->getName());

        $form = $this->createCreateForm($entity);

        $form->handleRequest($request);

        $success = false;
        $completeUrl = null;

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            //redirect to the page of the pagePattern
            $completeUrl = $this->generateUrl('victoire_core_page_show', array('url' => $entity->getUrl()));

            $success = true;
        } else {

            //get the errors as a string
            $errorMessage = $this->container->get('victoire_form.error_helper')->getRecursiveReadableErrors($form);
        }

        return new JsonResponse(array(
            'success' => $success,
            'url'     => $completeUrl,
            'message' => $errorMessage
        ));
    }

    /**
     * Creates a form to create a BusinessEntityPagePattern entity.
     *
     * @param BusinessEntityPagePattern $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     *
     * @return Form
     */
    private function createCreateForm(BusinessEntityPagePattern $entity)
    {
        $businessEntityName = $entity->getBusinessEntityName();
        $businessProperty = $this->getBusinessProperties($entity);

        $form = $this->createForm(
            'victoire_business_entity_page_type',
            $entity,
            array(
                'action'           => $this->generateUrl('victoire_businessentitypagepattern_businessentitypagepattern_create', array('id' => $businessEntityName)),
                'method'           => 'POST',
                'businessProperty' => $businessProperty
            )
        );

        return $form;
    }

    /**
     * Displays a form to create a new BusinessEntityPagePattern entity.
     * @param string $id The id of the businessEntity
     *
     * @Route("/{id}/new", name="victoire_businessentitypagepattern_businessentitypagepattern_new")
     * @Method("GET")
     * @Template()
     *
     * @return array The entity and the form
     */
    public function newAction($id)
    {
        //get the business entity
        $businessEntity = $this->getBusinessEntity($id);

        $entity = new BusinessEntityPagePattern();
        $entity->setBusinessEntityName($businessEntity->getName());

        $form = $this->createCreateForm($entity);

        $businessEntityHelper = $this->get('victoire_business_entity_page.business_entity_page_helper');
        $businessProperties = $businessEntityHelper->getBusinessProperties($businessEntity);

        $parameters = array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'businessProperties' => $businessProperties
        );

        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityPageBundle:BusinessEntityPagePattern:new.html.twig',
                $parameters
            ),
            'success' => true
        ));
    }

    /**
     * Displays a form to edit an existing BusinessEntityPagePattern entity.
     * @param string $id The id of the businessEntity
     *
     * @Route("/{id}/edit", name="victoire_businessentitypagepattern_businessentitypagepattern_edit")
     * @Method("GET")
     * @Template()
     *
     * @return array The entity and the form
     *
     * @throws \Exception
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $businessEntityPagePatternHelper = $this->get('victoire_business_entity_page.business_entity_page_helper');
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');

        $entity = $em->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPagePattern')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BusinessEntityPagePattern entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        //the business property link to the page
        $businessEntityId = $entity->getBusinessEntityName();
        $businessEntity = $businessEntityHelper->findById($businessEntityId);

        $businessEntityPagePatternHelper = $this->get('victoire_business_entity_page.business_entity_page_helper');

        $businessProperties = $businessEntityPagePatternHelper->getBusinessProperties($businessEntity);

        $parameters = array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'businessProperties' => $businessProperties
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
    * @param BusinessEntityPagePattern $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(BusinessEntityPagePattern $entity)
    {
        $businessProperty = $this->getBusinessProperties($entity);

        $form = $this->createForm('victoire_business_entity_page_type', $entity, array(
            'action' => $this->generateUrl('victoire_businessentitypagepattern_businessentitypagepattern_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'businessProperty' => $businessProperty
        ));

        return $form;
    }
    /**
     * Edits an existing BusinessEntityPagePattern entity.
     * @param Request $request
     * @param string  $id
     *
     * @Route("/{id}", name="victoire_businessentitypagepattern_businessentitypagepattern_update")
     * @Method("PUT")
     * @Template("VictoireBusinessEntityPageBundle:BusinessEntityPagePattern:edit.html.twig")
     *
     * @return array The parameter for the response
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

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($pagePattern);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            //get the url of the template
            $pagePattern = $pagePattern->getUrl();

            //the shortcuts service
            $shortcuts = $this->get('av.shortcuts');

            //redirect to the page of the template
            $completeUrl = $shortcuts->generateUrl('victoire_core_page_show', array('url' => $pagePattern));

            $success = true;
        } else {
            $success = false;
            $completeUrl = null;
        }

        return new JsonResponse(array(
            'success' => $success,
            'url'     => $completeUrl
        ));
    }

    /**
     * Deletes a BusinessEntityPagePattern entity.
     * @param Request $request
     * @param string  $id
     *
     * @Route("/{id}", name="victoire_businessentitypagepattern_businessentitypagepattern_delete")
     * @Method("DELETE")
     *
     * @throws \Exception
     *
     * @return redirect
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPagePattern')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find BusinessEntityPagePattern entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('victoire_businessentitypage_businessentity_index'));
    }

    /**
     * Creates a form to delete a BusinessEntityPagePattern entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('victoire_businessentitypagepattern_businessentitypagepattern_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }

    /**
     * List the entities that matches the query of the businessEntityPagePattern
     * @param BusinessEntityPagePattern $entity
     *
     * @Route("listEntities/{id}", name="victoire_businessentitypagepattern_businessentitypagepattern_listentities")
     * @ParamConverter("id", class="VictoireBusinessEntityPageBundle:BusinessEntityPagePattern")
     * @return array The list of items for this template
     *
     * @throws Exception
     */
    public function listEntitiesAction(BusinessEntityPagePattern $entity)
    {
        //services
        $businessEntityPagePatternHelper = $this->get('victoire_business_entity_page.business_entity_page_helper');

        $entities = $businessEntityPagePatternHelper->getEntitiesAllowed($entity);

        //parameters for the view
        $parameters = array(
            'businessEntityPagePattern' => $entity,
            'items'                     => $entities
        );

        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityPageBundle:BusinessEntityPagePattern:listEntities.html.twig',
                $parameters
            ),
            'success' => true
        ));
    }

    /**
     * Get an array of business properties by the business entity page pattern
     *
     * @param BusinessEntityPagePattern $entity
     *
     * @return array of business properties
     */
    private function getBusinessProperties(BusinessEntityPagePattern $entity)
    {
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');
        //the name of the business entity link to the business entity page pattern
        $businessEntityName = $entity->getBusinessEntityName();

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
