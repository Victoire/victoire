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
use Victoire\Bundle\SeoBundle\Entity\Redirection;
use Victoire\Bundle\SeoBundle\Form\RedirectionType;
use Victoire\Bundle\SeoBundle\Repository\RedirectionRepository;

/**
 * Class RedirectionController.
 *
 * @Route("/redirection")
 */
class RedirectionController extends Controller
{
    /**
     * @Route("/index", name="victoire_redirection_index")
     *
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var RedirectionRepository $redirectionRepository */
        $redirectionRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('VictoireSeoBundle:Redirection');

        $adapter = new DoctrineORMAdapter($redirectionRepository->getUnresolvedQuery());
        $pager = new Pagerfanta($adapter);

        $pager->setMaxPerPage(100);
        $pager->setCurrentPage($request->query->get('page', 1));

        $forms = [];

        /** @var Redirection $redirection */
        foreach ($pager->getCurrentPageResults() as $redirection) {
            $forms[$redirection->getId()] = $this->getRedirectionForm($redirection, sprintf('#redirection-%d-item-container', $redirection->getId()))->createView();
        }

        $newForm = $this->getRedirectionForm(new Redirection(), '#new-form-container')->createView();

        return $this->render($this->getBaseTemplatePath().':index.html.twig', [
            'newForm' => $newForm,
            'forms'   => $forms,
            'pager'   => $pager,
        ]);
    }

    /**
     * @Route("/{id}/save", name="victoire_redirection_save")
     *
     * @Method("POST")
     *
     * @param Request     $request
     * @param Redirection $redirection
     *
     * @return JsonResponse|Response
     */
    public function saveAction(Request $request, Redirection $redirection)
    {
        $form = $this->getRedirectionForm($redirection, sprintf('#redirection-%d-item-container', $redirection->getId()));

        $form->handleRequest($request);
        if ($request->query->get('novalidate', false) === false) {
            if ($form->isValid()) {
                if ($redirection->getLink()->getLinkType() !== Link::TYPE_NONE) {
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                } else {
                    // force form error when linkType === none
                    $form->addError(new FormError('This value should not be blank.'));
                }
            }

            return new Response($this->renderView('@VictoireSeo/Redirection/_item.html.twig', [
                'redirection' => $redirection,
                'form'        => $form->createView(),
                'isOpened'    => true,
            ]));
        }

        // rebuild form to avoid wrong form error
        $form = $this->getRedirectionForm($redirection, sprintf('#redirection-%d-item-container', $redirection->getId()));
        // refresh redirection.link to avoid empty link datas
        $this->getDoctrine()->getManager()->refresh($redirection->getLink());

        return new JsonResponse([
            'html' => $this->renderView('@VictoireSeo/Redirection/_item.html.twig', [
                'form'        => $form->createView(),
                'redirection' => $redirection,
                'isOpened'    => true,
            ]),
        ]);
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
        $redirection = new Redirection();

        $form = $this->getRedirectionForm($redirection, '#new-form-container');
        $form->handleRequest($request);

        if ($request->query->get('novalidate', false) === false) {
            if ($form->isValid()) {
                if ($redirection->getLink()->getLinkType() !== Link::TYPE_NONE) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($redirection);
                    $em->flush();

                    return new Response($this->renderView('@VictoireSeo/Redirection/_form.html.twig', [
                        'form'      => $this->getRedirectionForm(new Redirection(), '#new-form-container')->createView(),
                        'icTrigger' => [
                            'target' => '#redirections-list-container',
                            'url'    => $this->generateUrl('victoire_redirection_showListItem', [
                                'id' => $redirection->getId(),
                            ]),
                        ],
                    ]));
                } else {
                    // force form error when no link submitted
                    $form->addError(new FormError('This value should not be blank.'));
                }
            }

            return new Response($this->renderView('@VictoireSeo/Redirection/_form.html.twig', [
                'form' => $form->createView(),
            ]));
        }

        // rebuild form to avoid wrong form error
        $form = $this->getRedirectionForm($redirection, '#new-form-container');

        return new JsonResponse([
            'html' => $this->renderView('@VictoireSeo/Redirection/_form.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
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

        return new Response(null, 204, [
            'X-IC-Remove' => '100ms',
        ]);
    }

    /**
     * @Route("/{id}/showListItem", name="victoire_redirection_showListItem")
     *
     * @Method("GET")
     *
     * @param Redirection $redirection
     *
     * @return JsonResponse|Response
     */
    public function showListItemAction(Redirection $redirection)
    {
        $form = $this->getRedirectionForm($redirection, sprintf('#redirection-%d-item-container', $redirection->getId()));

        return $this->render('@VictoireSeo/Redirection/_item.html.twig', [
            'redirection' => $redirection,
            'form'        => $form->createView(),
        ]);
    }

    /**
     * @param Redirection $redirection
     * @param string      $containerId
     *
     * @return \Symfony\Component\Form\Form
     */
    private function getRedirectionForm(Redirection $redirection, $containerId)
    {
        $action = (null === $redirection->getId())
            /* update redirection */
            ? $this->generateUrl('victoire_redirection_new')
            /* create redirection */
            : $this->generateUrl('victoire_redirection_save', ['id' => $redirection->getId()]);

        return $this->createForm(RedirectionType::class, $redirection, [
            'method'      => 'POST',
            'action'      => $action,
            'containerId' => $containerId,
            'withUrl'     => (null === $redirection->getId()) ? true : false,
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
        return 'VictoireSeoBundle:Redirection';
    }
}
