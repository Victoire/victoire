<?php

namespace Victoire\Widget\LightSaberBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Annotations as VIC;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * WidgetLightSaber.
 *
 * @ORM\Table("vic_widget_lightsaber")
 * @ORM\Entity
 */
class WidgetLightSaber extends Widget
{
    /**
     * @var int
     *
     * @VIC\ReceiverProperty("imageable", required=true)
     * @ORM\Column(name="length", type="integer", nullable=true)
     */
    protected $length;

    /**
     * @var string
     *
     * @ORM\Column(name="crystal", type="string", length=255)
     */
    protected $crystal;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=255)
     */
    protected $color;

    /**
     * To String function.
     *
     * @return string
     */
    public function __toString()
    {
        return 'LightSaber #'.$this->id;
    }

    /**
     * Set length.
     *
     * @param string $length
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length.
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set crystal.
     *
     * @param string $crystal
     */
    public function setCrystal($crystal)
    {
        $this->crystal = $crystal;

        return $this;
    }

    /**
     * Get crystal.
     *
     * @return string
     */
    public function getCrystal()
    {
        return $this->crystal;
    }

    /**
     * Set color.
     *
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color.
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }
}
