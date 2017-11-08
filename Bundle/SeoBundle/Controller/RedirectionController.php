<?php

namespace Victoire\Bundle\SeoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\SeoBundle\Entity\Redirection;
use Victoire\Bundle\SeoBundle\Form\RedirectionType;

/**
 * Class RedirectionController
 *
 * @Route("/redirection")
 */
class RedirectionController extends Controller
{
    use VictoireAlertifyControllerTrait;

    /**
     * @Route("/index", name="victoire_redirection_index")
     *
     * @Method("GET")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->generateView();
    }

    /**
     * @Route("/{id}/update", name="victoire_redirection_update")
     *
     * @Method("POST")
     *
     * @param Request $request
     * @param Redirection $redirection
     *
     * @return Response
     */
    public function updateAction(Request $request, Redirection $redirection)
    {
        $form = $this->createForm(RedirectionType::class, $redirection);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($redirection);
                $em->flush();

                $this->congrat('La redirection a été modifiée avec succès');

                return $this->generateView();
            }
        }

        $this->warn('Une erreur est survenue, veuillez réessayer');

        return $this->generateView();
    }

    /**
     * @Route("/new", name="victoire_redirection_new")
     *
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(RedirectionType::class, new Redirection());

        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                $redirection = $form->getData();

                $redirection->setStatusCode(Response::HTTP_MOVED_PERMANENTLY);
                $redirection->setCount(0);

                $em->persist($redirection);
                $em->flush();

                $this->congrat('La redirection a été créée avec succès');

                return $this->generateView();
            }
        }

        $this->warn('Une erreur est survenue, veuillez réessayer');

        return $this->generateView();
    }

    /**
     * @Route("/{id}/delete", name="victoire_redirection_delete")
     *
     * @Method("DELETE")
     *
     * @param Redirection $redirection
     *
     * @return Response
     */
    public function deleteAction(Redirection $redirection)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($redirection);
        $em->flush();

        $this->congrat('La redirection a été supprimée avec succès');

        return $this->generateView();
    }

    /**
     * Build index view.
     *
     * @return Response
     */
    private function generateView()
    {
        $em = $this->getDoctrine()->getManager();

        $redirections = $em->getRepository('VictoireSeoBundle:Redirection')->findBy(
            ['statusCode' => 301],
            ['count' => 'DESC']
        );

        $listForm = [];

        /**
         * Build error list form
         *
         * @var Redirection $redirection
         */
        foreach ($redirections as $redirection) {
            $redirectionId = $redirection->getId();
            $listForm[$redirectionId] = $this->createForm(RedirectionType::class, $redirection, [
                'action' => $this->generateUrl('victoire_redirection_update', [
                    'id' => $redirectionId
                ]),
                'attr' => [
                    'ic-post-to' => $this->generateUrl('victoire_redirection_update', [
                        'id' => $redirectionId
                    ]),
                    'ic-target' => '#vic-modal-container'
                ]
            ])->createView();
        }

        /**
         * Build new redirection form
         */
        $newForm = $this->createForm(new RedirectionType(), new Redirection(), [
            'action' => $this->generateUrl('victoire_redirection_new'),
            'attr' => [
                'ic-post-to' => $this->generateUrl('victoire_redirection_new'),
                'ic-target' => '#vic-modal-container'
            ]
        ])->createView();

        return $this->render($this->getBaseTemplatePath().':index.html.twig', [
            'redirections' => $redirections,
            'listForm'     => $listForm,
            'newForm'      => $newForm
        ]);
    }

    /**
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return 'VictoireSeoBundle:Redirection';
    }
}