<?php

namespace Victoire\Bundle\MediaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    /**
     * @param int $mediaId
     *
     * @Route("/{mediaId}", requirements={"mediaId" = "\d+"}, name="VictoireMediaBundle_media_show", options={"expose"=true})
     *
     * @return Response
     */
    public function showAction($mediaId)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        /* @var Media $media */
        $media = $em->getRepository('VictoireMediaBundle:Media')->getMedia($mediaId);
        $folder = $media->getFolder();

        /* @var MediaManager $mediaManager */
        $mediaManager = $this->get('victoire_media.media_manager');
        $handler = $mediaManager->getHandler($media);
        $helper = $handler->getFormHelper($media);

        $form = $this->createForm($handler->getFormType(), $helper);

        if ('POST' == $request->getMethod()) {
            $form->bind($request);
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

        $this->get('session')->getFlashBag()->add('success', 'Entry \''.$medianame.'\' has been deleted!');

        return new RedirectResponse($this->generateUrl('VictoireMediaBundle_folder_show', ['folderId'  => $folder->getId()]));
    }

    /**
     * @param int $folderId
     *
     * @Route("bulkupload/{folderId}", requirements={"folderId" = "\d+"}, name="VictoireMediaBundle_media_bulk_upload")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function bulkUploadAction(Request $request, $folderId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);

        $helper = new BulkUploadHelper();

        $form = $this->createForm(new BulkUploadType('*/*'), $helper);

        if ('POST' == $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                foreach ($helper->getFiles() as $file) {
                    /* @var Media $media */
                    $media = $this->get('victoire_media.media_manager')->getHandler($file)->createNew($file);
                    $media->setFolder($folder);
                    $em->getRepository('VictoireMediaBundle:Media')->save($media);
                }

                $this->get('session')->getFlashBag()->add('success', 'New entry has been uploaded');

                return new RedirectResponse($this->generateUrl('VictoireMediaBundle_folder_show', ['folderId'  => $folder->getId()]));
            }
        }

        $formView = $form->createView();
        $filesfield = $formView->children['files'];
        $filesfield->vars = array_replace($filesfield->vars, [
            'full_name' => 'victoire_mediabundle_bulkupload[files][]',
        ]);

        return [
            'form'      => $formView,
            'folder'    => $folder,
        ];
    }

    /**
     * @param int $folderId
     *
     * @Route("drop/{folderId}", requirements={"folderId" = "\d+"}, name="VictoireMediaBundle_media_drop_upload")
     * @Method({"GET", "POST"})
     *
     * @return Response
     */
    public function dropAction($folderId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);

        $drop = null;
        if (isset($this->getRequest()->files) && array_key_exists('files', $this->getRequest()->files)) {
            $drop = $this->getRequest()->files->get('files');
        } else {
            $drop = $this->getRequest()->get('text');
        }
        $media = $this->get('victoire_media.media_manager')->createNew($drop);
        if ($media) {
            $media->setFolder($folder);
            $em->getRepository('VictoireMediaBundle:Media')->save($media);

            return new Response(json_encode(['status' => 'File was uploaded successfuly!']));
        }

        $this->getRequest()->getSession()->getFlashBag()->add('notice', 'Could not recognize what you dropped!');

        return new Response(json_encode(['status' => 'Could not recognize anything!']));
    }

    /**
     * @param int    $folderId The folder id
     * @param string $type     The type
     *
     * @Route("create/{folderId}/{type}", requirements={"folderId" = "\d+", "type" = ".+"}, name="VictoireMediaBundle_media_create")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function createAction($folderId, $type)
    {
        return $this->createAndRedirect($folderId, $type, 'VictoireMediaBundle_folder_show');
    }

    /**
     * @param int    $folderId The folder id
     * @param string $type     The type
     *
     * @Route("create/modal/{folderId}/{type}", requirements={"folderId" = "\d+", "type" = ".+"}, name="VictoireMediaBundle_media_modal_create")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function createModalAction($folderId, $type)
    {
        return $this->createAndRedirect($folderId, $type, 'VictoireMediaBundle_chooser_show_folder');
    }

    /**
     * @param int    $folderId    The folder Id
     * @param string $type        The type
     * @param string $redirectUrl The url where we want to redirect to on success
     *
     * @return array
     */
    private function createAndRedirect($folderId, $type, $redirectUrl)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        /* @var Folder $folder */
        $folder = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);

        /* @var MediaManager $mediaManager */
        $mediaManager = $this->get('victoire_media.media_manager');
        $handler = $mediaManager->getHandlerForType($type);
        $media = new Media();
        $helper = $handler->getFormHelper($media);

        $form = $this->createForm($handler->getFormType(), $helper);

        if ('POST' == $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                $media = $helper->getMedia();
                $media->setFolder($folder);
                $em->getRepository('VictoireMediaBundle:Media')->save($media);

                $this->get('session')->getFlashBag()->add('success', 'Media \''.$media->getName().'\' has been created!');

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
