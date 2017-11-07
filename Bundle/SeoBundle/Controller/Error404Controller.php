<?php

namespace Victoire\Bundle\SeoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\SeoBundle\Entity\Redirection;
use Victoire\Bundle\SeoBundle\Form\RedirectionListType;
use Victoire\Bundle\SeoBundle\Model\RedirectionList;

/**
 * Class Error404Controller.
 *
 * @Route("/error404")
 */
class Error404Controller extends Controller
{
    /**
     * @Route("/index", name="victoire_404_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository('VictoireSeoBundle:Redirection')->findBy(
            ['statusCode' => 404],
            ['count' => 'DESC']
        );

        $list = new RedirectionList();

        foreach ($errors as $error) {
            $list->addRedirection($error);
        }

        /**
         * Build error list form
         */
        $form = $this->createForm(new RedirectionListType(), $list, [
            'action' => $this->generateUrl('victoire_404_update'),
            'method' => 'POST',
            'attr' => [
                'ic-post-to' => $this->generateUrl('victoire_404_update'),
                'ic-target-id' => '#vic-modal-container'
            ]
        ]);

        return $this->render($this->getBaseTemplatePath().':index.html.twig', [
            'errors' => $errors,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/update", name="victoire_404_update")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

//        var_dump($request);
//        die();

        $form = $this->createForm(new RedirectionListType());

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                $errorsList = $form->getData();
                $errors = $errorsList->getRedirections();

                /** @var Redirection $error */
                foreach ($errors as $error) {
                    if ($error->getOutput()) {

                        $error->setStatusCode(Response::HTTP_MOVED_PERMANENTLY);
                        $error->setCount(0);

                        $em->persist($error);
                    }
                }
                $em->flush();

                return $this->redirectToRoute('victoire_404_index');
            }
            $errors = $form->getErrors()->valid();
            $errors2 = $form->isSubmitted();

            var_dump($errors);
            var_dump($errors2);
            die();

            // warn user
        }

        return $this->redirectToRoute('victoire_404_index');
//        return new Response(null, 302, [
//            'X-IC-Redirect' => $this->generateUrl('victoire_404_index')
//        ]);
    }

    /**
     * @Route("/{id}/delete", name="victoire_404_delete")
     *
     * @Template()
     *
     * @param Redirection $redirection
     *
     * @return JsonResponse
     */
    public function deleteAction(Redirection $redirection)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($redirection);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'url'     => $this->generateUrl('victoire_404_index'),
        ]);
    }

    /**
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return 'VictoireSeoBundle:404';
    }
}