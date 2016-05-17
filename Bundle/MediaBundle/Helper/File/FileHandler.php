<?php

namespace Victoire\Bundle\MediaBundle\Helper\File;

use Gaufrette\Adapter\Local;
use Gaufrette\Filesystem;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\FileBinaryMimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Victoire\Bundle\MediaBundle\Entity\Media;
use Victoire\Bundle\MediaBundle\Form\File\FileType;
use Victoire\Bundle\MediaBundle\Helper\Media\AbstractMediaHandler;

/**
 * FileHandler.
 */
class FileHandler extends AbstractMediaHandler
{
    /**
     * @var string
     */
    const TYPE = 'file';

    /**
     * @var Filesystem
     */
    public $fileSystem = null;

    /**
     * @var MimeTypeGuesserInterface
     */
    public $mimeTypeGuesser = null;
    public $urlizer;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->fileSystem = new Filesystem(new \Gaufrette\Adapter\Local('uploads/media/', true));
        //we use a specific symfony mimetypeguesser because de default (FileinfoMimeTypeGuesser) is unable to recognize MS documents
        $this->mimeTypeGuesser = new FileBinaryMimeTypeGuesser();
        $this->urlizer = new Urlizer();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'File Handler';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @return FileType
     */
    public function getFormType()
    {
        return FileType::class;
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    public function canHandle($object)
    {
        if ($object instanceof File || ($object instanceof Media && (is_file($object->getContent()) || $object->getLocation() == 'local'))) {
            return true;
        }

        return false;
    }

    /**
     * @param Media $media
     *
     * @return FileHelper
     */
    public function getFormHelper(Media $media)
    {
        return new FileHelper($media);
    }

    /**
     * @param Media $media
     *
     * @throws \RuntimeException when the file does not exist
     */
    public function prepareMedia(Media $media)
    {
        if (null === $media->getUuid()) {
            $uuid = uniqid();
            $media->setUuid($uuid);
        }
        $content = $media->getContent();
        if (empty($content)) {
            return;
        }
        if (!$content instanceof File) {
            if (!is_file($content)) {
                throw new \RuntimeException('Invalid file');
            }
            $file = new File($content);
            $media->setContent($file);
        }
        if ($content instanceof UploadedFile) {
            $pathInfo = pathinfo($content->getClientOriginalName());
            $media->setOriginalFilename($this->urlizer->urlize($pathInfo['filename']).'.'.$pathInfo['extension']);
            $name = $media->getName();
            if (empty($name)) {
                $media->setName($media->getOriginalFilename());
            }
        }
        $media->setFileSize(filesize($media->getContent()));
        $contentType = $this->mimeTypeGuesser->guess($media->getContent()->getPathname());
        $media->setContentType($contentType);
        $relativePath = sprintf('/%s.%s', $media->getUuid(), ExtensionGuesser::getInstance()->guess($media->getContentType()));
        $media->setUrl('/uploads/media'.$relativePath);
        $media->setLocation('local');
    }

    /**
     * @param Media $media
     */
    public function saveMedia(Media $media)
    {
        if (!$media->getContent() instanceof File) {
            return;
        }

        $originalFile = $this->getOriginalFile($media);
        $originalFile->setContent(file_get_contents($media->getContent()->getRealPath()));
    }

    /**
     * @param Media $media
     *
     * @return \Gaufrette\File
     */
    public function getOriginalFile(Media $media)
    {
        $relativePath = sprintf('/%s.%s', $media->getUuid(), ExtensionGuesser::getInstance()->guess($media->getContentType()));

        return $this->fileSystem->get($relativePath, true);
    }

    /**
     * @param Media $media
     */
    public function removeMedia(Media $media)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateMedia(Media $media)
    {
        $this->saveMedia($media);
    }

    /**
     * @param File $data
     *
     * @return Media
     */
    public function createNew($data)
    {
        if ($data instanceof File) {
            /* @var $data File */

            $media = new Media();
            if (method_exists($media, 'getClientOriginalName')) {
                $media->setName($data->getClientOriginalName());
            } else {
                $media->setName($data->getFilename());
            }
            $media->setContent($data);

            return $media;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getShowTemplate(Media $media)
    {
        return 'VictoireMediaBundle:Media\File:show.html.twig';
    }

    /**
     * @return array
     */
    public function getAddFolderActions()
    {
        return [
                self::TYPE => [
                        'type' => self::TYPE,
                        'name' => 'media.file.add', ],
        ];
    }
}
