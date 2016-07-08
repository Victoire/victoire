<?php

namespace Victoire\Bundle\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Media.
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\MediaBundle\Repository\MediaRepository")
 * @ORM\Table(name="vic_media")
 * @ORM\HasLifecycleCallbacks
 */
class Media
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, length=255)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $uuid;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, name="original_filename")
     */
    protected $originalFilename;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="location", nullable=true)
     */
    protected $location;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="content_type")
     */
    protected $contentType;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $metadata = [];

    /**
     * @var Folder
     *
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="media")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id")
     */
    protected $folder;

    /**
     * @var mixed
     */
    protected $content;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $filesize;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $deleted;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
        $this->deleted = false;
    }

    /**
     * Return string representation of entity; we return the url display directly the media without using any getter.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->url;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id The unique identifier
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFileSize()
    {
        $size = $this->filesize;
        if ($size < 1024) {
            return $size.'b';
        } else {
            $help = $size / 1024;
            if ($help < 1024) {
                return round($help, 1).'kb';
            } else {
                return round(($help / 1024), 1).'mb';
            }
        }
    }

    /**
     * @param int $filesize
     */
    public function setFileSize($filesize)
    {
        $this->filesize = $filesize;
    }

    /**
     * Set uuid.
     *
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Get uuid.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param string $originalFilename
     *
     * @return Media
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;
        return $this;
    }
    /**
     * @return string
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * Set location.
     *
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get location.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set contentType.
     *
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Get contentType.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Get contentType.
     *
     * @return string
     */
    public function getContentTypeShort()
    {
        $contentType = $this->contentType;
        $array = explode('/', $contentType);
        $contentType = end($array);

        return $contentType;
    }

    /**
     * Set metadata.
     *
     * @param array $metadata
     *
     * @return Media
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get metadata.
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set the specified metadata value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return Media
     */
    public function setMetadataValue($key, $value)
    {
        $this->metadata[$key] = $value;

        return $this;
    }

    /**
     * Get the specified metadata value.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getMetadataValue($key)
    {
        return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
    }

    /**
     * Set content.
     *
     * @param mixed $content
     *
     * @return Media
     */
    public function setContent($content)
    {
        $this->content = $content;
        $this->setUpdatedAt(new \DateTime());

        return $this;
    }

    /**
     * Get content.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set folder.
     *
     * @param Folder $folder
     *
     * @return Media
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder.
     *
     * @return Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getClassType()
    {
        $class = explode('\\', get_class($this));
        $classname = end($class);

        return $classname;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (empty($this->name)) {
            $this->setName($this->getOriginalFilename());
        }
    }
}
