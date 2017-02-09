<?php

namespace Victoire\Bundle\WidgetMapBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * @ORM\Table("vic_widget_map")
 * @ORM\Entity()
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
     * @ORM\JoinColumn(name="view_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $view;

    /**
     * @var View
     */
    protected $viewContext;

    /**
     * @var [Widget]
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetBundle\Entity\Widget", mappedBy="widgetMap", orphanRemoval=true, cascade={"persist", "remove"})
     */
    protected $widgets;

    /**
     * @deprecated
     *
     * @var Widget
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\WidgetBundle\Entity\Widget")
     * @ORM\JoinColumn(name="widget_id", referencedColumnName="id", onDelete="SET NULL")
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
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
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
        $this->substitutes = new ArrayCollection();
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
     * @return string
     */
    public function isAsynchronous()
    {
        return $this->asynchronous;
    }

    /**
     * @param bool|string $asynchronous
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
     * @return [Widget]
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @param Widget $widget
     *
     * @return $this
     */
    public function addWidget(Widget $widget)
    {
        $this->widgets[] = $widget;

        return $this;
    }

    /**
     * @param [Widget] $widgets
     *
     * @return $this
     */
    public function setWidgets($widgets)
    {
        $this->widgets = $widgets;

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
     * Get the current View context.
     *
     * @return View
     */
    public function getViewContext()
    {
        return $this->viewContext;
    }

    /**
     * Store the current View context.
     *
     * @param View $viewContext
     *
     * @return $this
     */
    public function setViewContext(View $viewContext)
    {
        $this->viewContext = $viewContext;

        return $this;
    }

    /**
     * @return WidgetMap
     */
    public function getReplaced()
    {
        return $this->replaced;
    }

    /**
     * @param WidgetMap $replaced
     */
    public function setReplaced($replaced)
    {
        if ($replaced) {
            $replaced->addSubstitute($this);
        }
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
    public function getChildren(View $view = null)
    {
        $positions = [self::POSITION_BEFORE, self::POSITION_AFTER];
        $children = [];
        $widgetMap = $this;
        foreach ($positions as $position) {
            $children[$position] = null;
            if (($childs = $widgetMap->getChilds($position)) && !empty($childs)) {
                foreach ($childs as $_child) {
                    // found child must belongs to the given view or one of it's templates
                    if ($view) {
                        // if child view is same as given view or the child view is a template of given view
                        if ($_child->getView() && ($view === $_child->getView() || $_child->getView()->isTemplateOf($view))) {
                            // if child is a substitute in view
                            if ($substitute = $_child->getSubstituteForView($view)) {
                                // if i'm not the parent of the substitute or i does not have the same position, child is not valid
                                if ($substitute->getParent() !== $this || $substitute->getPosition() !== $position) {
                                    $_child = null;
                                }
                            }
                            $children[$position] = $_child;
                        }
                    } else {
                        $children[$position] = $_child;
                    }
                }
            }
            // If I am replaced and my replacement has children for the position
            if (!$children[$position]
                && ($replaced = $this->getReplaced())
                && !empty($this->getReplaced()->getChilds($position))) {
                foreach ($this->getReplaced()->getChilds($position) as $_child) {
                    if ($view) {
                        // if child view is same as given view or the child view is a template of given view
                        if ($_child->getView() && ($view === $_child->getView() || $_child->getView()->isTemplateOf($view))) {
                            // if child is a substitute in view
                            if ($substitute = $_child->getSubstituteForView($view)) {
                                // if i'm not the parent of the substitute or i does not have the same position, child is not valid
                                if ($substitute->getParent() != $this || $substitute->getPosition() != $position) {
                                    $_child = null;
                                }
                            }
                            $children[$position] = $_child;
                        }
                    } else {
                        $children[$position] = $_child;
                    }
                }
            }
        }

        return $children;
    }

    /**
     * @return Collection
     */
    public function getChildrenRaw()
    {
        return $this->children;
    }

    public function hasChild($position, View $view = null)
    {
        foreach ($this->getChildren($view) as $child) {
            if ($child && $child->getPosition() === $position) {
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
            if ($_child && $_child->getPosition() == $position) {
                $child = $_child;
            }
        }

        return $child;
    }

    /**
     * Return all children from ViewContext (already loaded WidgetMaps).
     *
     * @return WidgetMap[]
     */
    public function getChilds($position)
    {
        $childsWidgetMaps = [];
        $viewWidgetMaps = $this->getViewContext()->getWidgetMapsForViewAndTemplates();

        foreach ($viewWidgetMaps as $viewWidgetMap) {
            if ($viewWidgetMap->getParent() == $this && $viewWidgetMap->getPosition() == $position) {
                $childsWidgetMaps[] = $viewWidgetMap;
            }
        }

        return $childsWidgetMaps;
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
     * @param null|WidgetMap $parent
     */
    public function setParent(WidgetMap $parent = null)
    {
        if ($this->parent) {
            $this->parent->removeChild($this);
        }
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
     * Return all substitutes from ViewContext (already loaded WidgetMaps)
     * Ideally must return only one WidgetMap per View.
     *
     * @return WidgetMap[]
     */
    public function getSubstitutes()
    {
        $substitutesWidgetMaps = [];
        $viewWidgetMaps = $this->getViewContext()->getWidgetMapsForViewAndTemplates();

        foreach ($viewWidgetMaps as $viewWidgetMap) {
            if ($viewWidgetMap->getReplaced() == $this) {
                $substitutesWidgetMaps[] = $viewWidgetMap;
            }
        }

        return $substitutesWidgetMaps;
    }

    /**
     * Return substitute if used in View.
     *
     * @return WidgetMap|null
     */
    public function getSubstituteForView(View $view)
    {
        foreach ($this->getSubstitutes() as $substitute) {
            if ($substitute->getView() === $view) {
                return $substitute;
            }
        }
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

    /**
     * @deprecated
     *
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @deprecated
     *
     * @param Widget $widget
     *
     * @return WidgetMap
     */
    public function setWidget(Widget $widget = null)
    {
        $this->widget = $widget;

        return $this;
    }
}
