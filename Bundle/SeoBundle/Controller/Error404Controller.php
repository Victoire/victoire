<?php

namespace Victoire\Bundle\SeoBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\CoreBundle\Entity\Link;
use Victoire\Bundle\SeoBundle\Entity\Error404;
use Victoire\Bundle\SeoBundle\Entity\ErrorRedirection;
use Victoire\Bundle\SeoBundle\Form\RedirectionType;
use Victoire\Bundle\SeoBundle\Repository\HttpErrorRepository;

/**
 * Class Error404Controller
 *
 * @Route("/error404")
 */
class Error404Controller extends Controller
{
    /**
     * @Route("/index", name="victoire_404_index")
     *
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var HttpErrorRepository $error404Repository */
        $error404Repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('VictoireSeoBundle:HttpError');

        /* todo fix pager */
        $adapter = new DoctrineORMAdapter($error404Repository->getUnresolvedQuery());
        $pager   = new Pagerfanta($adapter);

        $pager->setMaxPerPage(100);
        $pager->setCurrentPage($request->query->get('page', 1));

        $forms = [];

        /** @var Error404 $error */
        foreach ($pager->getCurrentPageResults() as $error) {
            $redirection = new ErrorRedirection();
            $redirection->setError($error);
            $forms[$error->getId()] = $this->getError404RedirectionForm($redirection)->createView();
        }

        return $this->render($this->getBaseTemplatePath().':index.html.twig', [
            'pager' => $pager,
            'forms' => $forms
        ]);
    }

    /**
     * @Route("/{id}/redirect", name="victoire_404_redirect")
     *
     * @Method("POST")
     *
     * @param Request  $request
     * @param Error404 $error404
     *
     * @return JsonResponse|Response
     */
    public function redirectAction(Request $request, Error404 $error404)
    {
        $redirection = new ErrorRedirection();
        $redirection->setError($error404);

        $form = $this->getError404RedirectionForm($redirection);

        $form->handleRequest($request);
        if ($request->query->get('novalidate', false) === false) {
            if ($form->isValid()) {
                if ($redirection->getLink()->getLinkType() !== Link::TYPE_NONE) {
                    $em = $this->getDoctrine()->getManager();
                    $error404->setRedirection($redirection);

                    $em->persist($redirection);
                    $em->flush();

                    return new Response(null, 204, [
                        'X-IC-Remove' => '100ms'
                    ]);
                } else {
                    // force form error when no linkType === none
                    $form->addError(new FormError('This value should not be blank.'));
                }
            }

            return new Response($this->renderView('@VictoireSeo/Error404/_item.html.twig', [
                'form'     => $form->createView(),
                'error'    => $error404,
                'isOpened' => true
            ]));
        }

        // rebuild form to avoid wrong form error
        $form = $this->getError404RedirectionForm($redirection);

        return new JsonResponse([
            'html' => $this->renderView('@VictoireSeo/Error404/_item.html.twig', [
                'form'     => $form->createView(),
                'error'    => $error404,
                'isOpened' => true
            ])
        ]);
    }

    /**
     * @Route("/{id}/delete", name="victoire_404_delete")
     *
     * @Method("DELETE")
     *
     * @param Error404 $error404
     *
     * @return Response
     */
    public function deleteAction(Error404 $error404)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($error404);
        $em->flush();

        return new Response(null, 204, [
            'X-IC-Remove' => '100ms'
        ]);
    }

    /**
     * @param ErrorRedirection $redirection
     *
     * @return \Symfony\Component\Form\Form
     */
    private function getError404RedirectionForm(ErrorRedirection $redirection)
    {
        $containerId = sprintf('#404-%d-item-container', $redirection->getError()->getId());

        $action = $this->generateUrl('victoire_404_redirect', ['id' => $redirection->getError()->getId()]);

        return $this->createForm(RedirectionType::class, $redirection, [
            'method'      => 'POST',
            'action'      => $action,
            'containerId' => $containerId,
            'attr'        => [
                'ic-post-to' => $action,
                'ic-target'  => $containerId,
            ]
        ]);
    }

    /**
     * @return string
     */
    protected function getBaseTemplatePath()
    {
        return 'VictoireSeoBundle:Error404';
    }
}