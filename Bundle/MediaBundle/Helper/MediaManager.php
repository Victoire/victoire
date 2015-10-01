<?php

namespace Victoire\Bundle\MediaBundle\Helper;

use Victoire\Bundle\MediaBundle\Entity\Media;
use Victoire\Bundle\MediaBundle\Helper\File\FileHandler;
use Victoire\Bundle\MediaBundle\Helper\Media\AbstractMediaHandler;

/**
 * MediaManager.
 */
class MediaManager
{
    /**
     * @var AbstractMediaHandler[]
     */
    protected $handlers = [];

    /**
     * @param AbstractMediaHandler $handler Media handler
     *
     * @return void
     */
    public function addHandler(AbstractMediaHandler $handler)
    {
        $this->handlers[$handler->getName()] = $handler;
    }

    /**
     * Returns handler to handle the Media item which can handle the item. If no handler is found, it returns FileHandler.
     *
     * @param Media $media
     *
     * @return AbstractMediaHandler
     */
    public function getHandler($media)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($media)) {
                return $handler;
            }
        }

        return new FileHandler();
    }

    /**
     * Returns handler to handle the Media item based on the Type. If no handler is found, it returns FileHandler.
     *
     * @param string $type
     *
     * @return AbstractMediaHandler
     */
    public function getHandlerForType($type)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->getType() == $type) {
                return $handler;
            }
        }

        return new FileHandler();
    }

    /**
     * @return AbstractMediaHandler[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param \Victoire\Bundle\MediaBundle\Entity\Media $media
     */
    public function prepareMedia(Media $media)
    {
        $handler = $this->getHandler($media);
        $handler->prepareMedia($media);
    }

    /**
     * @param Media $media The media
     * @param bool  $new   Is new
     */
    public function saveMedia(Media $media, $new = false)
    {
        $handler = $this->getHandler($media);

        if ($new) {
            $handler->saveMedia($media);
        } else {
            $handler->updateMedia($media);
        }
    }

    /**
     * @param \Victoire\Bundle\MediaBundle\Entity\Media $media
     */
    public function removeMedia(Media $media)
    {
        $handler = $this->getHandler($media);
        $handler->removeMedia($media);
    }

    /**
     * @param mixed $data
     *
     * @return Media
     */
    public function createNew($data)
    {
        foreach ($this->handlers as $handler) {
            $result = $handler->createNew($data);
            if ($result) {
                return $result;
            }
        }

        return;
    }

    /**
     * @return array
     */
    public function getFolderAddActions()
    {
        $result = [];
        foreach ($this->handlers as $handler) {
            $actions = $handler->getAddFolderActions();
            if ($actions) {
                $result = array_merge($actions, $result);
            }
        }

        return $result;
    }
}
