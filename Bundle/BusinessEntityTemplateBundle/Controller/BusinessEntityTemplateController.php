<?php

namespace Victoire\Bundle\BusinessEntityTemplateBundle\Controller;

use Victoire\Bundle\BusinessEntityTemplateBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate;
use Victoire\Bundle\BusinessEntityTemplateBundle\Form\BusinessEntityTemplateType;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * BusinessEntityTemplate controller.
 *
 * @Route("/victoire-dcms/business-entity-template/businessentitytemplate")
 */
class BusinessEntityTemplateController extends BaseController
{
    /**
     * Creates a new BusinessEntityTemplate entity.
     *
     * @Route("/", name="victoire_businessentitytemplate_businessentitytemplate_create")
     * @Method("POST")
     * @Template("VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate:new.html.twig")
     *
     * @param Request $request
     * @return multitype:\Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate NULL
     */
    public function createAction(Request $request)
    {
        $entity = new BusinessEntityTemplate();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            //get the associated page
            $page = $entity->getPage();

            //get the url of the page
            $pageurl = $page->getUrl();

            //redirect to the page of the template
            $completeUrl = '/'.$pageurl;
            $success = true;
        } else {
            $success = false;
            $completeUrl = null;
        }

        return new JsonResponse(array(
            'success' => $success,
            'url' => $completeUrl
        ));
    }

    /**
     * Creates a form to create a BusinessEntityTemplate entity.
     *
     * @param BusinessEntityTemplate $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     *
     * @return Form
     */
    private function createCreateForm(BusinessEntityTemplate $entity)
    {
        $form = $this->createForm('victoire_business_entity_template_type', $entity, array(
            'action' => $this->generateUrl('victoire_businessentitytemplate_businessentitytemplate_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Displays a form to create a new BusinessEntityTemplate entity.
     *
     * @Route("/{id}/new", name="victoire_businessentitytemplate_businessentitytemplate_new")
     * @Method("GET")
     * @Template()
     *
     * @param string $id The id of the businessEntity
     *
     * @return array The entity and the form
     */
    public function newAction($id)
    {
        //get the business entity
        $businessEntity = $this->getBusinessEntity($id);

        $entity = new BusinessEntityTemplate();
        $entity->setBusinessEntity($businessEntity);

        $form = $this->createCreateForm($entity);

        $parameters = array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate:new.html.twig',
                $parameters
            ),
            'success' => true
        ));
    }


    /**
     * Displays a form to edit an existing BusinessEntityTemplate entity.
     *
     * @Route("/{id}/edit", name="victoire_businessentitytemplate_businessentitytemplate_edit")
     * @Method("GET")
     * @Template()
     *
     * @param string $id The id of the businessEntity
     *
     * @return array The entity and the form
     *
     * @throws \Exception
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BusinessEntityTemplate entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        $parameters = array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );

        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate:edit.html.twig',
                $parameters
            ),
            'success' => true
        ));
    }

    /**
    * Creates a form to edit a BusinessEntityTemplate entity.
    *
    * @param BusinessEntityTemplate $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(BusinessEntityTemplate $entity)
    {
        $form = $this->createForm('victoire_business_entity_template_type', $entity, array(
            'action' => $this->generateUrl('victoire_businessentitytemplate_businessentitytemplate_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        return $form;
    }
    /**
     * Edits an existing BusinessEntityTemplate entity.
     *
     * @Route("/{id}", name="victoire_businessentitytemplate_businessentitytemplate_update")
     * @Method("PUT")
     * @Template("VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate:edit.html.twig")
     *
     * @param Request $request
     * @param string $id
     * @return array The parameter for the response
     *
     * @throws \Exception
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BusinessEntityTemplate entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            //get the associated page
            $page = $entity->getPage();

            //get the url of the page
            $pageurl = $page->getUrl();

            //redirect to the page of the template
            $completeUrl = '/'.$pageurl;
            $success = true;
        } else {
            $success = false;
            $completeUrl = null;
        }

        return new JsonResponse(array(
            'success' => $success,
            'url' => $completeUrl
        ));
    }

    /**
     * Deletes a BusinessEntityTemplate entity.
     *
     * @Route("/{id}", name="victoire_businessentitytemplate_businessentitytemplate_delete")
     * @Method("DELETE")
     *
     * @param Request $request
     * @param string $id
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
            $entity = $em->getRepository('VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find BusinessEntityTemplate entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('businessentitytemplate'));
    }

    /**
     * Creates a form to delete a BusinessEntityTemplate entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('victoire_businessentitytemplate_businessentitytemplate_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
