<?php

namespace Acme\AppBundle\Controller;

use Acme\AppBundle\Entity\SpaceShip;
use Acme\AppBundle\Form\Type\SpaceShipType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\CoreBundle\Controller\BackendController;

/**
 * SpaceShip controller.
 * Generated with generate:crud command.
 *
 * @Route("/spaceship")
 */
class SpaceShipController extends BackendController
{
    /**
     * Lists all SpaceShip entities.
     *
     * @Route("/", name="acme_app_spaceship_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AcmeAppBundle:SpaceShip')->findAll();

        return [
            'entities' => $entities,
        ];
    }

    /**
     * Creates a new SpaceShip entity.
     *
     * @Route("/", name="acme_app_spaceship_create")
     * @Method("POST")
     * @Template("AcmeAppBundle:SpaceShip:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new SpaceShip();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('acme_app_spaceship_index'));
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }

    /**
     * Creates a form to create a SpaceShip entity.
     *
     * @param SpaceShip $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(SpaceShip $entity)
    {
        $form = $this->createForm(SpaceShipType::class, $entity, [
            'action' => $this->generateUrl('acme_app_spaceship_create'),
            'method' => 'POST',
        ]);

        $form->add('submit', 'submit', ['label' => 'acme.app.spaceship.form.button.create']);

        return $form;
    }

    /**
     * Displays a form to create a new SpaceShip entity.
     *
     * @Route("/new", name="acme_app_spaceship_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new SpaceShip();
        $form = $this->createCreateForm($entity);

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }

    /**
     * Finds and displays a SpaceShip entity.
     *
     * @Route("/{id}", name="acme_app_spaceship_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AcmeAppBundle:SpaceShip')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SpaceShip entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing SpaceShip entity.
     *
     * @Route("/{id}/edit", name="acme_app_spaceship_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AcmeAppBundle:SpaceShip')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SpaceShip entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity'      => $entity,
            'form'        => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Creates a form to edit a SpaceShip entity.
     *
     * @param SpaceShip $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(SpaceShip $entity)
    {
        $form = $this->createForm(SpaceShipType::class, $entity, [
            'action' => $this->generateUrl('acme_app_spaceship_update', ['id' => $entity->getId()]),
            'method' => 'PUT',
        ]);

        $form->add('submit', 'submit', ['label' => 'acme.app.spaceship.form.button.update']);

        return $form;
    }

    /**
     * Edits an existing SpaceShip entity.
     *
     * @Route("/{id}", name="acme_app_spaceship_update")
     * @Method("PUT")
     * @Template("AcmeAppBundle:SpaceShip:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AcmeAppBundle:SpaceShip')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SpaceShip entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('acme_app_spaceship_index'));
        }

        return [
            'entity'      => $entity,
            'form'        => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a SpaceShip entity.
     *
     * @Route("/{id}", name="acme_app_spaceship_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AcmeAppBundle:SpaceShip')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find SpaceShip entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('acme_app_spaceship_index'));
    }

    /**
     * Creates a form to delete a SpaceShip entity by id.
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
            ->setAction($this->generateUrl('acme_app_spaceship_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'acme.app.spaceship.form.button.delete'])
            ->getForm();
    }
}
