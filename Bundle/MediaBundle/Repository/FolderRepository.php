<?php

namespace Victoire\Bundle\MediaBundle\Repository;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\MediaBundle\Entity\Folder;

/**
 * FolderRepository.
 */
class FolderRepository extends EntityRepository
{
    /**
     * @param Folder $folder The folder
     */
    public function save(Folder $folder)
    {
        $em = $this->getEntityManager();

        $em->persist($folder);
        $em->flush();
    }

    /**
     * @param Folder $folder
     */
    public function delete(Folder $folder)
    {
        $em = $this->getEntityManager();

        $this->deleteMedia($folder, $em);
        $this->deleteChildren($folder, $em);
        $folder->setDeleted(true);
        $em->persist($folder);
        $em->flush();
    }

    /**
     * @param Folder $folder
     */
    public function deleteMedia(Folder $folder)
    {
        $em = $this->getEntityManager();

        foreach ($folder->getMedia() as $item) {
            $item->setDeleted(true);
            $em->persist($item);
            $em->remove($item);
        }
    }

    /**
     * @param Folder $folder
     */
    public function deleteChildren(Folder $folder)
    {
        $em = $this->getEntityManager();

        foreach ($folder->getChildren() as $child) {
            $this->deleteMedia($child, $em);
            $this->deleteChildren($child, $em);
            $child->setDeleted(true);
            $em->persist($child);
        }
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    public function getAllFolders($limit = null)
    {
        $qb = $this->createQueryBuilder('folder')->select('folder')->where('folder.parent is null AND folder.deleted != true')->orderby('folder.name');
        if (false === is_null($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $folderId
     *
     * @throws EntityNotFoundException
     *
     * @return Folder
     */
    public function getFolder($folderId)
    {
        $em = $this->getEntityManager();

        $folder = $em->getRepository('VictoireMediaBundle:Folder')->find($folderId);
        if (!$folder) {
            throw new EntityNotFoundException('The id given for the folder is not valid.');
        }

        return $folder;
    }

    public function getFirstTopFolder()
    {
        $em = $this->getEntityManager();

        $folder = $em->getRepository('VictoireMediaBundle:Folder')->findOneBy(['parent' => null]);
        if (!$folder) {
            $folder = new Folder();
            $folder->setId(1);
            $folder->setName('Root folder');
            $em->persist($folder);
            $em->flush();
        }

        return $folder;
    }
}
