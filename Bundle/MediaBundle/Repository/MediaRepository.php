<?php

namespace Victoire\Bundle\MediaBundle\Repository;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\MediaBundle\Entity\Media;

/**
 * MediaRepository.
 */
class MediaRepository extends EntityRepository
{
    /**
     * @param Media $media
     */
    public function save(Media $media)
    {
        $em = $this->getEntityManager();

        $em->persist($media);
        $em->flush();
    }

    /**
     * @param Media $media
     */
    public function delete(Media $media)
    {
        $em = $this->getEntityManager();

        $media->setDeleted(true);
        $em->persist($media);
        $em->flush();
    }

    /**
     * @param int $mediaId
     *
     * @throws EntityNotFoundException
     *
     * @return object
     */
    public function getMedia($mediaId)
    {
        $em = $this->getEntityManager();

        $media = $em->getRepository('VictoireMediaBundle:Media')->find($mediaId);
        if (!$media) {
            throw new EntityNotFoundException('The id given for the media is not valid.');
        }

        return $media;
    }

    /**
     * @param int $pictureId
     *
     * @throws EntityNotFoundException
     *
     * @return object
     */
    public function getPicture($pictureId)
    {
        $em = $this->getEntityManager();

        $picture = $em->getRepository('VictoireMediaBundle:Image')->find($pictureId);
        if (!$picture) {
            throw new EntityNotFoundException('Unable to find image.');
        }

        return $picture;
    }
}
