<?php

namespace Victoire\Bundle\MediaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\MediaBundle\Entity\Folder;
use Victoire\Bundle\MediaBundle\Form\FolderType;

/**
 * folder controller.
 *
 * @Route("/victoire-media/folder")
 */
class FolderController extends Controller
{
    /**
     * @param int $folderId The folder id
     *
     * @Route("/{folderId}", requirements={"folderId" = "\d+"}, name="VictoireMediaBundle_folder_show")
     * @Template()
     *
     * @return array
     */
    public function showAction($folderId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);
        $folders = $em->getRepository('VictoireMediaBundle:Folder')->getAllFolders();

        $sub = new Folder();
        $sub->setParent($folder);
        $subForm = $this->createForm(new FolderType($sub), $sub);
        $editForm = $this->createForm(new FolderType($folder), $folder);

        return [
            'mediamanager'  => $this->get('victoire_media.media_manager'),
            'subform'       => $subForm->createView(),
            'editform'      => $editForm->createView(),
            'folder'        => $folder,
            'folders'       => $folders,
        ];
    }

    /**
     * @param int $folderId
     *
     * @Route("/delete/{folderId}", requirements={"folderId" = "\d+"}, name="VictoireMediaBundle_folder_delete")
     *
     * @return RedirectResponse
     */
    public function deleteAction($folderId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);
        $folderName = $folder->getName();
        $parentFolder = $folder->getParent();

        if (empty($parentFolder)) {
            $this->get('session')->getFlashBag()->add('failure', 'You can\'t delete the \''.$folderName.'\' Folder!');
        } else {
            $folder->setDeleted(true);
            $em->persist($folder);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Folder \''.$folderName.'\' has been deleted!');
            $folderId = $parentFolder->getId();
        }

        return new RedirectResponse($this->generateUrl('VictoireMediaBundle_folder_show', ['folderId'  => $folderId]));
    }

    /**
     * @param int $folderId
     *
     * @Route("/subcreate/{folderId}", requirements={"folderId" = "\d+"}, name="VictoireMediaBundle_folder_sub_create")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @return Response
     */
    public function subCreateAction($folderId)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        /* @var Folder $parent */
        $parent = $em->getRepository('VictoireMediaBundle:Folder')->getFolder($folderId);
        $folder = new Folder();
        $folder->setParent($parent);
        $form = $this->createForm(new FolderType(), $folder);
        if ('POST' == $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->getRepository('VictoireMediaBundle:Folder')->save($folder);

                $this->get('session')->getFlashBag()->add('success', 'Folder \''.$folder->getName().'\' has been created!');

                return new Response('<script>window.location="'.$this->generateUrl('VictoireMediaBundle_folder_show', ['folderId' => $folder->getId()]).'"</script>');
            }
        }

        $galleries = $em->getRepository('VictoireMediaBundle:Folder')->getAllFolders();

        return $this->render('VictoireMediaBundle:Folder:addsub-modal.html.twig', [
            'subform'   => $form->createView(),
            'galleries' => $galleries,
            'folder'    => $folder,
            'parent'    => $parent,
        ]);
    }
}
