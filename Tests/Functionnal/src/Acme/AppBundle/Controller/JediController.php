<?php

namespace Acme\AppBundle\Controller;

use Acme\AppBundle\Entity\Jedi;
use Acme\AppBundle\Form\JediType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\CoreBundle\Controller\BackendController;

/**
 * Jedi controller.
 * Generated with generate:crud command.
 *
 * @Route("/jedi")
 */
class JediController extends BackendController
{
    /**
     * Lists all Jedi entities.
     *
     * @Route("/", name="acme_app_jedi_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AcmeAppBundle:Jedi')->findAll();

        return [
            'entities' => $entities,
        ];
    }

    /**
     * Creates a new Jedi entity.
     *
     * @Route("/", name="acme_app_jedi_create")
     * @Method("POST")
     * @Template("AcmeAppBundle:Jedi:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Jedi();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('acme_app_jedi_index'));
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }

    /**
     * Creates a form to create a Jedi entity.
     *
     * @param Jedi $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Jedi $entity)
    {
        $form = $this->createForm(new JediType(), $entity, [
            'action' => $this->generateUrl('acme_app_jedi_create'),
            'method' => 'POST',
        ]);

        $form->add('submit', 'submit', ['label' => 'acme.app.jedi.form.button.create']);

        return $form;
    }

    /**
     * Displays a form to create a new Jedi entity.
     *
     * @Route("/new", name="acme_app_jedi_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Jedi();
        $form = $this->createCreateForm($entity);

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }

    /**
     * Finds and displays a Jedi entity.
     *
     * @Route("/{id}", name="acme_app_jedi_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AcmeAppBundle:Jedi')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Jedi entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Jedi entity.
     *
     * @Route("/{id}/edit", name="acme_app_jedi_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AcmeAppBundle:Jedi')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Jedi entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Creates a form to edit a Jedi entity.
     *
     * @param Jedi $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Jedi $entity)
    {
        $form = $this->createForm(new JediType(), $entity, [
            'action' => $this->generateUrl('acme_app_jedi_update', ['id' => $entity->getId()]),
            'method' => 'PUT',
        ]);

        $form->add('submit', 'submit', ['label' => 'acme.app.jedi.form.button.update']);

        return $form;
    }

    /**
     * Edits an existing Jedi entity.
     *
     * @Route("/{id}", name="acme_app_jedi_update")
     * @Method("PUT")
     * @Template("AcmeAppBundle:Jedi:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AcmeAppBundle:Jedi')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Jedi entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('acme_app_jedi_index'));
        }

        return [
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Jedi entity.
     *
     * @Route("/{id}", name="acme_app_jedi_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AcmeAppBundle:Jedi')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Jedi entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('acme_app_jedi_index'));
    }

    /**
     * Creates a form to delete a Jedi entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(null, [
                'translation_domain' => 'victoire',
            ])
            ->setAction($this->generateUrl('acme_app_jedi_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'acme.app.jedi.form.button.delete'])
            ->getForm();
    }
}
