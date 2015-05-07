<?php
namespace Victoire\Widget\AnakinBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * WidgetAnakin
 *
 * @ORM\Table("vic_widget_anakin")
 * @ORM\Entity
 */
class WidgetAnakin extends Widget
{

    /**
     * @var string
     *
     * @ORM\Column(name="side", type="string", length=255)
     */
    protected $side;

    /**
     * To String function
     * Used in render choices type (Especially in VictoireWidgetRenderBundle)
     * //TODO Check the generated value and make it more consistent
     *
     * @return String
     */
    public function __toString()
    {
        return 'Anakin #'.$this->id;
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
