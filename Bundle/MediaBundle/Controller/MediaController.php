<?php

namespace Victoire\Bundle\MediaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\MediaBundle\Entity\Folder;
use Victoire\Bundle\MediaBundle\Entity\Media;
use Victoire\Bundle\MediaBundle\Form\BulkUploadType;
use Victoire\Bundle\MediaBundle\Helper\BulkUploadHelper;
use Victoire\Bundle\MediaBundle\Helper\MediaManager;

/**
 * MediaController.
 *
 * @Route("/victoire-media/media")
 */
class MediaController extends Controller
{
    use VictoireAlertifyControllerTrait;

    /**
     * @param Request $request
     * @param int     $mediaId
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     *
     * @return Response
     * @Route("/{mediaId}", requirements={"mediaId" = "\d+"}, name="VictoireMediaBundle_media_show", options={"expose"=true})
     */
    public function showAction(Request $request, $mediaId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Media $media */
        $media = $em->getRepository('VictoireMediaBundle:Media')->getMedia($mediaId);
        $folder = $media->getFolder();

        /* @var MediaManager $mediaManager */
        $mediaManager = $this->get('victoire_media.media_manager');
        $handler = $mediaManager->getHandler($media);
        $helper = $handler->getFormHelper($media);

        $form = $this->createForm($handler->getFormType(), $helper);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $media = $helper->getMedia();
                $em->getRepository('VictoireMediaBundle:Media')->save($media);

                return new RedirectResponse($this->generateUrl('VictoireMediaBundle_media_show', ['mediaId'  => $media->getId()]));
            }
        }
        $showTemplate = $mediaManager->getHandler($media)->getShowTemplate($media);

        return $this->render($showTemplate, [
                'handler'       => $handler,
                'mediamanager'  => $this->get('victoire_media.media_manager'),
                'editform'      => $form->createView(),
                'media'         => $media,
                'helper'        => $helper,
                'folder'        => $folder, ]);
    }

    /**
     * @param int $mediaId
     *
     * @Route("/delete/{mediaId}", requirements={"mediaId" = "\d+"}, name="VictoireMediaBundle_media_delete")
     *
     * @return RedirectResponse
     */
    public function deleteAction($mediaId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Media $media */
        $media = $em->getRepository('VictoireMediaBundle:Media')->getMedia($mediaId);
        $medianame = $media->getName();
        $folder = $media->getFolder();

        $em->getRepository('VictoireMediaBundle:Media')->delete($media);

        $this->congrat('Entry \''.$medianame.'\' has been deleted!');

        return new RedirectResponse($this->generateUrl('VictoireMediaBundle_folder_show', ['folderId'  => $folder->getId()]));
    }

    /**
     * @param Request $request
     * @param int     $folderId
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     *
     * @return array|RedirectResponse
     * @Route("bulkupload/{folderId}", requirements={"folderId" = "\d+"}, name="VictoireMediaBundle_media_bulk_upload")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function bulkUploadAction(Request $request, $folderId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);

        $helper = new BulkUploadHelper();

        $form = $this->createForm(BulkUploadType::class, $helper, ['accept' => '*/*']);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                foreach ($helper->getFiles() as $file) {
                    /* @var Media $media */
                    $media = $this->get('victoire_media.media_manager')->getHandler($file)->createNew($file);
                    $media->setFolder($folder);
                    $em->getRepository('VictoireMediaBundle:Media')->save($media);
                }

                $this->congrat('New entry has been uploaded');

                return new RedirectResponse($this->generateUrl('VictoireMediaBundle_folder_show', ['folderId'  => $folder->getId()]));
            }
        }

        $formView = $form->createView();
        $filesfield = $formView->children['files'];
        $filesfield->vars = array_replace($filesfield->vars, [
            'full_name' => 'mediabundle_bulkupload[files][]',
        ]);

        return [
            'form'      => $formView,
            'folder'    => $folder,
        ];
    }

    /**
     * @param Request $request
     * @param int     $folderId
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     *
     * @return Response
     * @Route("drop/{folderId}", requirements={"folderId" = "\d+"}, name="VictoireMediaBundle_media_drop_upload")
     * @Method({"GET", "POST"})
     */
    public function dropAction(Request $request, $folderId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);

        $drop = null;
        if (isset($request->files) && array_key_exists('files', $request->files)) {
            $drop = $request->files->get('files');
        } else {
            $drop = $request->get('text');
        }
        $media = $this->get('victoire_media.media_manager')->createNew($drop);
        if ($media) {
            $media->setFolder($folder);
            $em->getRepository('VictoireMediaBundle:Media')->save($media);

            return new Response(json_encode(['status' => 'File was uploaded successfuly!']));
        }

        $request->getSession()->getFlashBag()->add('notice', 'Could not recognize what you dropped!');

        return new Response(json_encode(['status' => 'Could not recognize anything!']));
    }

    /**
     * @param Request $request
     * @param int     $folderId The folder id
     * @param string  $type     The type
     *
     * @return array|RedirectResponse
     * @Route("create/{folderId}/{type}", requirements={"folderId" = "\d+", "type" = ".+"}, name="VictoireMediaBundle_media_create")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function createAction(Request $request, $folderId, $type)
    {
        return $this->createAndRedirect($request, $folderId, $type, 'VictoireMediaBundle_folder_show');
    }

    /**
     * @param Request $request
     * @param int     $folderId The folder id
     * @param string  $type     The type
     *
     * @return array|RedirectResponse
     * @Route("create/modal/{folderId}/{type}", requirements={"folderId" = "\d+", "type" = ".+"}, name="VictoireMediaBundle_media_modal_create")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function createModalAction(Request $request, $folderId, $type)
    {
        return $this->createAndRedirect($request, $folderId, $type, 'VictoireMediaBundle_chooser_show_folder');
    }

    /**
     * @param Request $request
     * @param int     $folderId    The folder Id
     * @param string  $type        The type
     * @param string  $redirectUrl The url where we want to redirect to on success
     *
     * @return array
     */
    private function createAndRedirect(Request $request, $folderId, $type, $redirectUrl)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);

        /* @var MediaManager $mediaManager */
        $mediaManager = $this->get('victoire_media.media_manager');
        $handler = $mediaManager->getHandlerForType($type);
        $media = new Media();
        $helper = $handler->getFormHelper($media);

        $options = array_merge([
            'action' => $this->generateUrl('VictoireMediaBundle_media_create', ['folderId' => $folderId, 'type' => $type]),
        ], $handler->getFormTypeOptions());
        $form = $this->createForm($handler->getFormType(), $helper, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $media = $helper->getMedia();
                $media->setFolder($folder);
                $em->getRepository('VictoireMediaBundle:Media')->save($media);

                $this->congrat('Media \''.$media->getName().'\' has been created!');

                return new RedirectResponse($this->generateUrl($redirectUrl, ['folderId' => $folder->getId()]));
            }
        }

        return [
            'type'   => $type,
            'form'   => $form->createView(),
            'folder' => $folder,
        ];
    }
}
