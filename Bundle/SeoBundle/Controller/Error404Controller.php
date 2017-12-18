<?php

namespace Victoire\Bundle\SeoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\CoreBundle\Entity\Link;
use Victoire\Bundle\SeoBundle\Entity\Error404;
use Victoire\Bundle\SeoBundle\Entity\ErrorRedirection;
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
        $forms = [];

        $errors = $this->getDoctrine()->getManager()->getRepository('VictoireSeoBundle:HttpError')->findBy([
            'redirection' => null
        ]);

        /** @var Error404 $error */
        foreach ($errors as $error) {
            $redirection = new ErrorRedirection();
            $redirection->setError($error);
            $forms[$error->getId()] = $this->getError404RedirectionForm($redirection)->createView();
        }

        return new JsonResponse([
            'success' => true,
            'html'    => $this->get('templating')->render($this->getBaseTemplatePath().':index.html.twig', [
                'errors' => $errors,
                'forms'  => $forms
            ])
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
                    $error404->setRedirection($redirection);
                    $redirection->setError($error404);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($redirection);
                    $em->flush();

//                    $this->congrat($this->get('translator')->trans('alert.error_404.redirect.success'));

                    if (0 == count($em->getRepository('VictoireSeoBundle:HttpError')->findBy(['redirection' => null]))) {
                        return new Response($this->renderView('@VictoireSeo/Error404/_empty.html.twig'), 200, [
                            'X-Inject-Alertify' => true,
                        ]);
                    }

                    return new Response(null, 200, [
                        'X-IC-Remove' => '100ms',
                        'X-Inject-Alertify' => true,
                    ]);
                } else {
                    // force form error when linkType === none
                    $form->addError(new FormError('This value should not be blank.'));
                }
            } else {
//                $this->warn($this->get('translator')->trans('alert.error_404.form.error'));
            }

            return new Response($this->renderView('@VictoireSeo/Error404/_item.html.twig', [
                'form'     => $form->createView(),
                'error'    => $error404,
                'isOpened' => true,
            ]));
        }

        // rebuild form to avoid wrong form error
        $form = $this->getError404RedirectionForm($redirection);

        return new JsonResponse([
            'html' => $this->renderView('@VictoireSeo/Error404/_list.html.twig', [
                'form'     => $form->createView(),
                'error'    => $error404,
                'isOpened' => true,
            ]),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="victoire_404_delete")
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

//        $this->congrat($this->get('translator')->trans('alert.error_404.delete.success'));

//        if (0 == count($em->getRepository('VictoireSeoBundle:HttpError')->findBy(['redirection' => null]))) {
//            return new Response($this->renderView('@VictoireSeo/Error404/_empty.html.twig'), 200, [
//                'X-Inject-Alertify' => true,
//            ]);
//        }

        return new Response(null, 200, [
            'X-IC-Remove' => '100ms',
//            'X-Inject-Alertify' => true,
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
            ],
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