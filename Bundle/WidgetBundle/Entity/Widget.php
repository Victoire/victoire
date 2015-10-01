<?php

namespace Victoire\Bundle\WidgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\QueryBundle\Entity\Traits\QueryTrait;
use Victoire\Bundle\WidgetBundle\Entity\Traits\StyleTrait;
use Victoire\Bundle\WidgetBundle\Model\Widget as BaseWidget;

/**
 * Widget.
 *
 * @ORM\Table("vic_widget")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\WidgetBundle\Repository\WidgetRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 */
class Widget extends BaseWidget
{
    use StyleTrait;
    use QueryTrait;

    public function __construct()
    {
        $this->childrenSlot = uniqid();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="slot", type="string", length=255, nullable=true)
     */
    protected $slot;

    /**
     * @var string
     *
     * @ORM\Column(name="childrenSlot", type="string", length=100, nullable=true)
     */
    protected $childrenSlot;

    /**
     * @var string
     *
     * @ORM\Column(name="theme", type="string", length=255, nullable=true)
     */
    protected $theme;

    /**
     * @var string
     *
     * @ORM\Column(name="asynchronous", type="boolean", nullable=true)
     */
    protected $asynchronous;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\View", inversedBy="widgets", cascade={"persist"})
     * @ORM\JoinColumn(name="view_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $view;

    /**
     * @var string
     *
     * @ORM\Column(name="fields", type="array")
     */
    protected $fields = [];

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length=255, nullable=false)
     */
    protected $mode = self::MODE_STATIC;

    /**
     * Auto simple mode: joined entity.
     *
     * @var EntityProxy
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\EntityProxy", inversedBy="widgets", cascade={"persist"})
     * @ORM\JoinColumn(name="entityProxy_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $entityProxy;

    /**
     * @return string
     */
    public function getChildrenSlot()
    {
        return $this->childrenSlot ?: $this->getId();
    }

    /**
     * @param string $childrenSlot
     */
    public function setChildrenSlot($childrenSlot)
    {
        $this->childrenSlot = $childrenSlot;
    }
}
