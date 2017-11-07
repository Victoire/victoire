<?php

namespace Victoire\Bundle\SeoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\SeoBundle\Entity\Redirection;
use Victoire\Bundle\SeoBundle\Form\RedirectionListType;
use Victoire\Bundle\SeoBundle\Form\RedirectionType;
use Victoire\Bundle\SeoBundle\Model\RedirectionList;

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
     * @return Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $redirections = $em->getRepository('VictoireSeoBundle:Redirection')->findBy(
            ['statusCode' => 301],
            ['count' => 'DESC']
        );

        $list = new RedirectionList();

        foreach ($redirections as $redirection) {
            $list->addRedirection($redirection);
        }

        /**
         * Build redirection list form
         */
        $listForm = $this->createForm(new RedirectionListType(), $list, [
            'action' => $this->generateUrl('victoire_redirection_update'),
            'method' => 'POST'
        ]);

        /**
         * Build new redirection form
         */
        $newForm = $this->createForm(new RedirectionType(), new Redirection(), [
            'action' => $this->generateUrl('victoire_redirection_new'),
            'method' => 'POST',
            'attr' => [
                'ic-post-to' => $this->generateUrl('victoire_redirection_new'),
                'ic-target-id' => '#vic-modal-container'
            ]
        ]);

        return $this->render($this->getBaseTemplatePath().':index.html.twig', [
            'redirections' => $redirections,
            'newForm' => $newForm->createView(),
            'listForm' => $listForm->createView()
        ]);
    }

    /**
     * @Route("/update", name="victoire_redirection_update")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new RedirectionListType(), null);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $redirections = $form->getData();
            foreach ($redirections as $redirection) {
                $em->persist($redirection);
            }
            $em->flush();

//            return $this->indexAction();
//            return $this->redirectToRoute('victoire_redirection_index');
        }

        return new JsonResponse();
    }

    /**
     * @Route("/new", name="victoire_redirection_new")
     *
     * @Method({"POST", "GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new RedirectionType(), null);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                $redirection = $form->getData();

                $redirection->setStatusCode(Response::HTTP_MOVED_PERMANENTLY);
                $redirection->setCount(0);

                $em->persist($redirection);
                $em->flush();

                $this->congrat($this->get('translator')->trans('Success', [], 'victoire'));

                return $this->redirectToRoute('victoire_redirection_index');
            }

            $this->congrat($this->get('translator')->trans('Error', [], 'victoire'));
        }

        return $this->redirectToRoute('victoire_redirection_index');
//        return new JsonResponse([
//            'message' => 'error'
//        ]);
    }

    /**
     * @Route("/{id}/delete", name="victoire_redirection_delete")
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
            'url'     => $this->generateUrl('victoire_redirection_index'),
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