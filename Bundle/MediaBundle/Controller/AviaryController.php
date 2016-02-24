<?php

namespace Victoire\Bundle\MediaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\MediaBundle\Entity\Folder;
use Victoire\Bundle\MediaBundle\Entity\Media;
use Victoire\Bundle\MediaBundle\Helper\MediaManager;

/**
 * controllerclass which Aviary can use to upload the edited image and add it to the database.
 */
class AviaryController extends Controller
{
    /**
     * @param int $folderId The id of the Folder
     * @param int $mediaId  The id of the image
     *
     * @Route("/aviary/{folderId}/{mediaId}", requirements={"folderId" = "\d+", "mediaId" = "\d+"}, name="VictoireMediaBundle_aviary")
     *
     * @return RedirectResponse
     */
    public function indexAction(Request $request, $folderId, $mediaId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);
        /* @var Media $media */
        $media = $em->getRepository('VictoireMediaBundle:Media')->getMedia($mediaId);
        /* @var MediaManager $mediaManager */
        $mediaManager = $this->get('victoire_media.media_manager');

        $handler = $mediaManager->getHandler($media);
        $fileHelper = $handler->getFormHelper($media);
        $fileHelper->getMediaFromUrl($request->get('url'));
        $media = $fileHelper->getMedia();

        $em->persist($media);
        $em->flush();

        return new RedirectResponse($this->generateUrl('VictoireMediaBundle_folder_show', ['folderId' => $folder->getId()]));
    }
}
