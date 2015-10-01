<?php

namespace Victoire\Bundle\MediaBundle\Helper\Image;

use Symfony\Component\HttpFoundation\File\File;
use Victoire\Bundle\MediaBundle\Entity\Media;
use Victoire\Bundle\MediaBundle\Helper\File\FileHandler;

/**
 * FileHandler.
 */
class ImageHandler extends FileHandler
{
    protected $aviaryApiKey;

    /**
     * @param string $aviaryApiKey The aviary key
     */
    public function __construct($aviaryApiKey)
    {
        parent::__construct();
        $this->aviaryApiKey = $aviaryApiKey;
    }

    /**
     * @return string
     */
    public function getAviaryApiKey()
    {
        return $this->aviaryApiKey;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Image Handler';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'image';
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    public function canHandle($object)
    {
        if (parent::canHandle($object) && ($object instanceof File || strpos($object->getContentType(), 'image') === 0)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowTemplate(Media $media)
    {
        return 'VictoireMediaBundle:Media\Image:show.html.twig';
    }

    /**
     * @param Media  $media    The media entity
     * @param string $basepath The base path
     *
     * @return string
     */
    public function getImageUrl(Media $media, $basepath)
    {
        return $basepath.$media->getUrl();
    }
}
