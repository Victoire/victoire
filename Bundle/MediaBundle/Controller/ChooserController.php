<?php

namespace Victoire\Bundle\MediaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\MediaBundle\Entity\Folder;
use Victoire\Bundle\MediaBundle\Entity\Media;
use Victoire\Bundle\MediaBundle\Helper\MediaManager;

/**
 * chooser controller.
 *
 * @Route("/victoire-media/chooser")
 */
class ChooserController extends Controller
{
    /**
     * @Route("/", name="VictoireMediaBundle_chooser", options={"expose"=true})
     *
     * @param Request $request
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     *
     * @return RedirectResponse
     */
    public function chooserIndexAction(Request $request)
    {
        $type = $request->get('type');
        $cKEditorFuncNum = $request->get('CKEditorFuncNum');

        $em = $this->getDoctrine()->getManager();

        /* @var \Victoire\Bundle\MediaBundle\Entity\Folder $firstFolder */
        $firstFolder = $em->getRepository('VictoireMediaBundle:Folder')->getFirstTopFolder();

        return $this->redirect($this->generateUrl('VictoireMediaBundle_chooser_show_folder', ['folderId' => $firstFolder->getId(), 'type' => $type, 'CKEditorFuncNum' => $cKEditorFuncNum]));
    }

    /**
     * @param Request $request
     * @param int     $folderId The filder id
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     *
     * @return array
     * @Route("/{folderId}", requirements={"folderId" = "\d+"}, name="VictoireMediaBundle_chooser_show_folder")
     * @Template()
     */
    public function chooserShowFolderAction(Request $request, $folderId)
    {
        $type = $request->get('type');
        $cKEditorFuncNum = $request->get('CKEditorFuncNum');

        $em = $this->getDoctrine()->getManager();
        /* @var MediaManager $mediaHandler */
        $mediaHandler = $this->get('victoire_media.media_manager');

        /* @var \Victoire\Bundle\MediaBundle\Entity\Folder $folder */
        $folder = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);
        /* @var array $mediaHandler */
        $folders = $em->getRepository('VictoireMediaBundle:Folder')->getAllFolders();

        $handler = null;
        if ($type) {
            $handler = $mediaHandler->getHandlerForType($type);
        }

        return [
                'cKEditorFuncNum' => $cKEditorFuncNum,
                'mediamanager'    => $mediaHandler,
                'handler'         => $handler,
                'type'            => $type,
                'folder'          => $folder,
                'folders'         => $folders,
                'fileform'        => $this->createTypeFormView($mediaHandler, 'file'),
                'videoform'       => $this->createTypeFormView($mediaHandler, 'video'),
                'slideform'       => $this->createTypeFormView($mediaHandler, 'slide'),
        ];
    }

    /**
     * @param MediaManager $mediaManager
     * @param string       $type
     *
     * @return \Symfony\Component\Form\FormView
     */
    private function createTypeFormView(MediaManager $mediaManager, $type)
    {
        $handler = $mediaManager->getHandlerForType($type);
        $media = new Media();
        $helper = $handler->getFormHelper($media);

        return $this->createForm($handler->getFormType(), $helper)->createView();
    }
}
