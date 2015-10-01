<?php

namespace Victoire\Bundle\MediaBundle\Helper\Media;

use Symfony\Component\Form\AbstractType;
use Victoire\Bundle\MediaBundle\Entity\Media;

/**
 * AbstractMediaHandler.
 */
abstract class AbstractMediaHandler
{
    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @return AbstractType
     */
    abstract public function getFormType();

    /**
     * @param Media $media
     */
    abstract public function canHandle($media);

    /**
     * @param Media $media
     *
     * @return mixed
     */
    abstract public function getFormHelper(Media $media);

    /**
     * @param Media $media
     *
     * @return void
     */
    abstract public function prepareMedia(Media $media);

    /**
     * @param Media $media
     *
     * @return void
     */
    abstract public function saveMedia(Media $media);

    /**
     * @param Media $media
     *
     * @return void
     */
    abstract public function updateMedia(Media $media);

    /**
     * @param Media $media
     *
     * @return void
     */
    abstract public function removeMedia(Media $media);

    /**
     * {@inheritdoc}
     */
    public function getShowTemplate(Media $media)
    {
        return 'VictoireMediaBundle:Media:show.html.twig';
    }

    /**
     * @param Media  $media    The media entity
     * @param string $basepath The base path
     *
     * @return string
     */
    public function getImageUrl(Media $media, $basepath)
    {
        return;
    }

    /**
     * @return array
     */
    abstract public function getAddFolderActions();
}
