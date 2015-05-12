<?php

namespace Acme\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Victoire\Bundle\CoreBundle\Annotations as VIC;

/**
 * BrowseEvent
 *
 * @ORM\Entity
 * @ORM\Table("jedi")
 * @VIC\BusinessEntity({"Force"})
 */
class Jedi
{
    use \Gedmo\Timestampable\Traits\TimestampableEntity;
    use \Victoire\Bundle\CoreBundle\Entity\Traits\BusinessEntityTrait;

    /**
     * @return string
     */
    public function __toString() {
        return $this->name;
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @VIC\BusinessProperty({"textable", "businessParameter"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=55)
     * @VIC\BusinessProperty({"textable", "businessParameter"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="midi_chlorians", type="integer")
     * @VIC\BusinessProperty("textable")
     */
    private $midiChlorians;

    /**
     * @var string
     *
     * @ORM\Column(name="side", type="string", length=55)
     * @VIC\BusinessProperty("textable")
     */
    private $side;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"}, updatable=false, unique=false)
     * @ORM\Column(name="slug", type="string", length=255)
     * @VIC\BusinessProperty({"textable", "businessParameter"})
     */
    protected $slug;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get midiChlorians
     *
     * @return string
     */
    public function getMidiChlorians()
    {
        return $this->midiChlorians;
    }

    /**
     * Set midiChlorians
     * @param string $midiChlorians
     *
     * @return $this
     */
    public function setMidiChlorians($midiChlorians)
    {
        $this->midiChlorians = $midiChlorians;

        return $this;
    }

    /**
     * Get side
     *
     * @return string
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * Set side
     * @param string $side
     *
     * @return $this
     */
    public function setSide($side)
    {
        $this->side = $side;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set slug
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }
}
