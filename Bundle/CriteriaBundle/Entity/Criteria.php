<?php

namespace Victoire\Bundle\CriteriaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * Criteria
 *
 * @ORM\Table("vic_criteria")
 * @ORM\Entity
 */
class Criteria
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="operand", type="string", length=25)
     */
    private $operand;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
     * @var Widget
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\WidgetBundle\Entity\Widget", inversedBy="criterias")
     * @ORM\JoinColumn(name="widget_id", referencedColumnName="id", onDelete="SET NULL"))
     */
    protected $widget;


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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * Set operand
     *
     * @param string $operand
     *
     * @return Criteria
     */
    public function setOperand($operand)
    {
        $this->operand = $operand;

        return $this;
    }

    /**
     * Get operand
     *
     * @return string
     */
    public function getOperand()
    {
        return $this->operand;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Criteria
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param Widget $widget
     */
    public function setWidget($widget)
    {
        $this->widget = $widget;
    }

}

