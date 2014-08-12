<?php

namespace Victoire\Bundle\MediaBundle\Controller;

use Victoire\Bundle\MediaBundle\Helper\MediaManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Victoire\Bundle\MediaBundle\Entity\Folder;
use Victoire\Bundle\MediaBundle\Entity\Media;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * chooser controller.
 *
 */
class ChooserController extends Controller
{

    /**
     * @Route("/chooser", name="VictoireMediaBundle_chooser", options={"expose"=true})
     *
     * @return RedirectResponse
     */
    public function chooserIndexAction()
    {
        $type = $this->getRequest()->get('type');
        $cKEditorFuncNum = $this->getRequest()->get("CKEditorFuncNum");

        $em = $this->getDoctrine()->getManager();

        /* @var Folder $firstFolder */
        $firstFolder = $em->getRepository('VictoireMediaBundle:Folder')->getFirstTopFolder();

        return $this->redirect($this->generateUrl("VictoireMediaBundle_chooser_show_folder", array("folderId" => $firstFolder->getId(), "type" => $type, "CKEditorFuncNum" => $cKEditorFuncNum)));
    }

    /**
     * @param int $folderId The filder id
     *
     * @Route("/chooser/{folderId}", requirements={"folderId" = "\d+"}, name="VictoireMediaBundle_chooser_show_folder")
     * @Template()
     *
     * @return array
     */
    public function chooserShowFolderAction($folderId)
    {
        $type = $this->getRequest()->get('type');
        $cKEditorFuncNum = $this->getRequest()->get("CKEditorFuncNum");

        $em = $this->getDoctrine()->getManager();
        /* @var MediaManager $mediaHandler */
        $mediaHandler = $this->get('victoire_media.media_manager');

        /* @var Folder $folder */
        $folder = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);
        /* @var array $mediaHandler */
        $folders = $em->getRepository('VictoireMediaBundle:Folder')->getAllFolders();

        $handler = null;
        if ($type) {
            $handler = $mediaHandler->getHandlerForType($type);
        }

        return array(
                "cKEditorFuncNum" => $cKEditorFuncNum,
                'mediamanager' => $mediaHandler,
                'handler' => $handler,
                'type'    => $type,
                'folder'  => $folder,
                'folders' => $folders,
                'fileform' => $this->createTypeFormView($mediaHandler, "file"),
                'videoform' => $this->createTypeFormView($mediaHandler, "video"),
                'slideform' => $this->createTypeFormView($mediaHandler, "slide")
        );
    }

    /**
     * @param MediaManager $mediaManager
     * @param String       $type
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
