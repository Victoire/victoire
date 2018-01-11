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
use Victoire\Bundle\SeoBundle\Entity\HttpError;
use Victoire\Bundle\SeoBundle\Form\RedirectionType;
use Victoire\Bundle\SeoBundle\Repository\HttpErrorRepository;

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
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var HttpErrorRepository $errorRepository */
        $errorRepository = $this->getDoctrine()->getManager()->getRepository('VictoireSeoBundle:HttpError');

        // Fetch errors

        $routes = $errorRepository->getRouteErrors();
        $routesResults = $routes->getQuery()->getResult();

        $files = $errorRepository->getFileErrors();
        $filesResults = $files->getQuery()->getResult();

        // Build forms

        $forms = [];
        $errors = array_merge($routesResults, $filesResults);

        /** @var Error404 $error */
        foreach ($errors as $error) {
            $redirection = new ErrorRedirection();
            $redirection->setError($error);
            $forms[$error->getId()] = $this->getError404RedirectionForm($redirection)->createView();
        }

        // Return datas

        return new Response($this->renderView('@VictoireSeo/Error404/index.html.twig', [
            'routes' => $routesResults,
            'files'  => $filesResults,
            'forms'  => $forms,
        ]));
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
                    $redirection->setUrl($error404->getUrl());

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($redirection);
                    $em->flush();

                    $this->congrat($this->get('translator')->trans('victoire.404.redirection.success'));

                    return $this->returnAfterRemoval($error404);
                }

                $form->addError(new FormError($this->get('translator')->trans('victoire.404.form.error.blank')));
            }

            $this->warn($this->get('translator')->trans('victoire.404.form.error.unvalid'));

            return new Response($this->renderView('@VictoireSeo/Error404/_list.html.twig', [
                'form'     => $form->createView(),
                'error'    => $error404,
                'isOpened' => true,
            ]), 200, [
                'X-Inject-Alertify' => true,
            ]);
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

        $this->congrat($this->get('translator')->trans('victoire.404.delete.success'));

        return $this->returnAfterRemoval($error404);
    }

    /**
     * Remove error if there is more than one record, else return _empty template.
     *
     * @param Error404 $error404
     *
     * @return Response
     */
    private function returnAfterRemoval(Error404 $error404)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var HttpErrorRepository $errorRepository */
        $errorRepository = $em->getRepository('VictoireSeoBundle:HttpError');

        $errors = ($error404->getType() == HttpError::TYPE_ROUTE)
            ? $errorRepository->getRouteErrors()
            : $errorRepository->getFileErrors();

        if (0 == count($errors->getQuery()->getResult())) {
            return new Response($this->renderView('@VictoireSeo/Error404/_empty.html.twig'), 200, [
                'X-Inject-Alertify' => true,
            ]);
        }

        return new Response(null, 200, [
            'X-VIC-Remove'      => '100ms',
            'X-Inject-Alertify' => true,
        ]);
    }

    /**
     * @param ErrorRedirection $redirection
     *
     * @return \Symfony\Component\Form\Form
     */
    private function getError404RedirectionForm(ErrorRedirection $redirection)
    {
        $action = $this->generateUrl('victoire_404_redirect', ['id' => $redirection->getError()->getId()]);

        $containerId = sprintf('#404-%d-item-container', $redirection->getError()->getId());

        return $this->createForm(RedirectionType::class, $redirection, [
            'method'      => 'POST',
            'action'      => $action,
            'containerId' => $containerId,
            'attr'        => [
                'v-ic-post-to' => $action,
                'v-ic-target'  => 'closest li',
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
