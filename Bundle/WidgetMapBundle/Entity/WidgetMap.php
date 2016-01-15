<?php

namespace Victoire\Bundle\WidgetMapBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table("vic_widget_map")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\WidgetMapBundle\Repository\WidgetMapRepository")
 */
class WidgetMap
{
    const ACTION_CREATE = 'create';
    const ACTION_OVERWRITE = 'overwrite';
    const ACTION_DELETE = 'delete';

    const POSITION_BEFORE = 'before';
    const POSITION_AFTER = 'after';

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
     * @ORM\Column(name="action", type="string", length=255)
     */
    protected $action = null;

    /**
     * @var View
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\View", inversedBy="widgetMaps")
     * @ORM\JoinColumn(name="view_id", referencedColumnName="id")
     */
    protected $view;

    /**
     * @var Widget
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\WidgetBundle\Entity\Widget", inversedBy="widgetMaps")
     * @ORM\JoinColumn(name="widget_id", referencedColumnName="id")
     */
    protected $widget;

    /**
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap", inversedBy="substitutes")
     * @ORM\JoinColumn(name="replaced_id", referencedColumnName="id")
     */
    protected $replaced;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap", mappedBy="replaced")
     */
    protected $substitutes;

    /**
     * @var string
     *
     * @ORM\Column(name="asynchronous", type="boolean")
     */
    protected $asynchronous = false;

    /**
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", nullable=true)
     */
    protected $position;


    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap", mappedBy="parent")
     */
    protected $children;



    /**
     * @var string
     *
     * @ORM\Column(name="slot", type="string", length=255, nullable=true)
     */
    protected $slot;


    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->substitutes= new ArrayCollection();
    }
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return null
     */
    public function isAsynchronous()
    {
        return $this->asynchronous;
    }

    /**
     * @param null $asynchronous
     */
    public function setAsynchronous($asynchronous)
    {
        $this->asynchronous = $asynchronous;
    }

    /**
     * Set the action.
     *
     * @param string $action
     *
     * @throws \Exception The action is not valid
     */
    public function setAction($action)
    {
        //test validity of the action
        if ($action !== self::ACTION_CREATE && $action !== self::ACTION_OVERWRITE && $action !== self::ACTION_DELETE) {
            throw new \Exception('The action of the widget map is not valid. Action: ['.$action.']');
        }

        $this->action = $action;
    }

    /**
     * Get the action.
     *
     * @return string The action
     */
    public function getAction()
    {
        return $this->action;
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
     *
     * @return $this
     */
    public function setWidget(Widget $widget)
    {
        $this->widget = $widget;

        return $this;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param View $view
     */
    public function setView(View $view)
    {
        $this->view = $view;
    }

    /**
     * @return mixed
     */
    public function getReplaced()
    {
        return $this->replaced;
    }

    /**
     * @param mixed $replaced
     */
    public function setReplaced($replaced)
    {
        $replaced->addSubstitute($this);
        $this->replaced = $replaced;
    }

    /**
     * @return string
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * @param string $slot
     */
    public function setSlot($slot)
    {
        $this->slot = $slot;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        $positions = [WidgetMap::POSITION_BEFORE, WidgetMap::POSITION_AFTER];
        $children = [];
        $widgetMap = $this;
        foreach ($positions as $position) {
            if ($child = $widgetMap->getChild($position)) {
                $children[$position] = $child;
            }
            if (!$child
                && ($replaced = $this->getReplaced())
                && $this->getReplaced()->getChild($position) && !$this->getReplaced()->getChild($position)->getSubstituteForView($widgetMap->getView())) {
                $children[$position] = $replaced->getChild($position);
            }
        }

        return $children;
    }

    public function hasChild($position)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getPosition() === $position) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return WidgetMap|null
     */
    public function getChild($position)
    {
        $child = null;
        foreach ($this->children as $_child) {
            if ($_child->getPosition() == $position) {
                $child = $_child;
            }
        }


        return $child;
    }


    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return void
     */
    public function removeChildren()
    {
        foreach ($this->children as $child) {
            $child->setParent(null);
            $this->removeChild($child);
        }
    }
    /**
     * @param WidgetMap $child
     */
    public function addChild($child)
    {
        $this->children->add($child);
    }
    /**
     * @param WidgetMap $child
     */
    public function removeChild($child)
    {
        $this->children->removeElement($child);
    }

    /**
     * @return WidgetMap|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent(WidgetMap $parent = null)
    {
        if ($parent) {
            $parent->addChild($this);
        }
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getSubstitutes()
    {
        return $this->substitutes;
    }
    /**
     * @return mixed
     */
    public function getSubstituteForView(View $view)
    {
        foreach ($this->substitutes as $substitute) {
            if ($substitute->getView() == $view) {
                return $substitute;
            }
        }

        return null;
    }

    /**
     * @param WidgetMap $substitute
     */
    public function addSubstitute(WidgetMap $substitute)
    {
        $this->substitutes->add($substitute);
    }
    /**
     * @param [WidgetMap] $substitutes
     */
    public function setSubstitutes($substitutes)
    {
        $this->substitutes = $substitutes;
    }


}
