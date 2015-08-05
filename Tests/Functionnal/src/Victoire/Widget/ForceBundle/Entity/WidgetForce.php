<?php
namespace Victoire\Widget\ForceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Annotations as VIC;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * WidgetForce
 *
 * @ORM\Table("vic_widget_force")
 * @ORM\Entity
 */
class WidgetForce extends Widget
{
    /**
     * @var string
     *
     * @VIC\ReceiverProperty("textable")
     * @ORM\Column(name="side", type="string", length=255, nullable=true)
     */
    protected $side;

    /**
     * To String function
     *
     * @return String
     */
    public function __toString()
    {
	return 'Force #'.$this->id;
    }

    /**
     * Set side
     *
     * @param string $side
     */
    public function setSide($side)
    {
	$this->side = $side;

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
}
