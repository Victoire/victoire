<?php

namespace Victoire\Bundle\SeoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\SeoBundle\Entity\Redirection;
use Victoire\Bundle\SeoBundle\Form\RedirectionType;

/**
 * Class Error404Controller.
 *
 * @Route("/error404")
 */
class Error404Controller extends Controller
{
    use VictoireAlertifyControllerTrait;

    /**
     * @Route("/index", name="victoire_404_index")
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
     * @Route("/{id}/update", name="victoire_404_update")
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

                $redirection->setStatusCode(Response::HTTP_MOVED_PERMANENTLY);

                $em->persist($redirection);
                $em->flush();

                $this->congrat('L\'erreur 404 à bien été transformée en redirection');

                return $this->generateView();
            }

            $errors = $this->validatorMessageToString($redirection);
            $this->warn($errors);

            return $this->generateView();
        }

        $this->warn('Une erreur est survenue, veuillez réessayer');

        return $this->generateView();
    }

    /**
     * @Route("/{id}/delete", name="victoire_404_delete")
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

        $this->congrat('L\'erreur a bien été supprimée');

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

        $errors = $em->getRepository('VictoireSeoBundle:Redirection')->findBy(
            ['statusCode' => 404],
            ['count' => 'DESC']
        );

        $formArray = [];

        /**
         * Build error list form
         *
         * @var Redirection $error
         */
        foreach ($errors as $error) {
            $errorId = $error->getId();
            $formArray[$errorId] = $this->createForm(RedirectionType::class, $error, [
                'action' => $this->generateUrl('victoire_404_update', [
                    'id' => $errorId
                ]),
                'method' => 'POST',
                'attr' => [
                    'ic-post-to' => $this->generateUrl('victoire_404_update', [
                        'id' => $errorId
                    ]),
                    'ic-target' => '#vic-modal-container'
                ]
            ])->createView();
        }

        return $this->render($this->getBaseTemplatePath().':index.html.twig', [
            'errors' => $errors,
            'form' => $formArray
        ]);
    }

    /**
     * @param Redirection $redirection
     *
     * @return string
     */
    private function validatorMessageToString(Redirection $redirection){

        $validator = $this->get('validator');

        $errors = [];

        /**
         * @var ConstraintViolationInterface $error
         */
        foreach ($validator->validate($redirection) as $error){
            $errors[] = $error->getMessage();
        }

        return implode(" - ", $errors);
    }

    /**
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return 'VictoireSeoBundle:404';
    }
}